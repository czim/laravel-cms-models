<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationInterpreterInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\Support\Data\ModelActionReferenceData;
use Czim\CmsModels\Support\Data\ModelExportColumnData;
use Czim\CmsModels\Support\Data\ModelExportStrategyData;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Data\ModelListParentData;
use Czim\CmsModels\Support\Data\ModelScopeData;
use Czim\CmsModels\Support\Data\ModelShowFieldData;

class CmsModelInformationInterpreter implements ModelInformationInterpreterInterface
{

    /**
     * @var array|mixed
     */
    protected $raw;

    /**
     * Interprets raw CMS model information as a model information object.
     *
     * @param array $information
     * @return ModelInformationInterface|ModelInformation
     */
    public function interpret($information)
    {
        $this->raw = $information;

        $this->interpretListData()
             ->interpretFormData()
             ->interpretShowData()
             ->interpretExportData();

        return $this->createInformationInstance();
    }

    /**
     * @return ModelInformationInterface|ModelInformation
     */
    protected function createInformationInstance()
    {
        $info = (new ModelInformation([]))->clear();

        $info->setAttributes($this->raw);

        return $info;
    }


    /**
     * @return $this
     */
    protected function interpretListData()
    {
        if (array_has($this->raw, 'list') && is_array($this->raw['list'])) {

            $this->raw['list']['default_action'] = $this->normalizeStandardArrayProperty(
                array_get($this->raw['list'], 'default_action', []),
                'type',
                ModelActionReferenceData::class,
                'list.default_action'
            );

            $this->raw['list']['columns'] = $this->normalizeStandardArrayProperty(
                array_has($this->raw['list'], 'columns')
                    ?   array_get($this->raw['list'], 'columns', [])
                    :   array_get($this->raw['list'], 'fields', []),
                'strategy',
                ModelListColumnData::class,
                'list.columns'
            );
            unset($this->raw['list']['fields']);


            $filters = array_get($this->raw['list'], 'filters', []);
            if (false === $filters) {
                $this->raw['list']['disable_filters'] = true;
            } else {
                $this->raw['list']['filters'] = $this->normalizeStandardArrayProperty(
                    $filters,
                    'strategy',
                    ModelListFilterData::class,
                    'list.filters'
                );
            }


            $scopes = array_get($this->raw['list'], 'scopes', []);
            if (false === $scopes) {
                $this->raw['list']['disable_scopes'] = true;
            } else {
                $this->raw['list']['scopes'] = $this->normalizeScopeArray($scopes);
            }


            $parents = [];
            foreach (array_get($this->raw['list'], 'parents', []) as $key => $parent) {
                if ( ! is_string($parent)) {
                    $parents[ $key ] = $parent;
                } else {
                    $parents[ $key ] = new ModelListParentData([ 'relation' => $parent ]);
                }
            }
            $this->raw['list']['parents'] = $parents;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function interpretFormData()
    {
        if (array_has($this->raw, 'form') && is_array($this->raw['form'])) {

            $this->raw['form']['fields'] = $this->normalizeStandardArrayProperty(
                array_get($this->raw['form'], 'fields', []),
                'display_strategy',
                ModelFormFieldData::class,
                'form.fields'
            );


            $this->raw['form']['layout'] = array_get($this->raw['form'], 'layout', []);


            if (array_has($this->raw['form'], 'validation') && is_array($this->raw['form']['validation'])) {

                $this->raw['form']['validation']['create'] = array_get($this->raw['form']['validation'], 'create');
                $this->raw['form']['validation']['update'] = array_get($this->raw['form']['validation'], 'update');
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function interpretShowData()
    {
        if (array_has($this->raw, 'show') && is_array($this->raw['show'])) {

            $this->raw['show']['fields'] = $this->normalizeStandardArrayProperty(
                array_get($this->raw['show'], 'fields', []),
                'strategy',
                ModelShowFieldData::class,
                'show.fields'
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function interpretExportData()
    {
        if (array_has($this->raw, 'export') && is_array($this->raw['export'])) {

            $this->raw['export']['columns'] = $this->normalizeStandardArrayProperty(
                array_has($this->raw['export'], 'columns')
                    ?   array_get($this->raw['export'], 'columns', [])
                    :   array_get($this->raw['export'], 'fields', []),
                'strategy',
                ModelExportColumnData::class,
                'export.columns'
            );

            if ( ! array_has($this->raw['export'], 'strategies') || ! is_array($this->raw['export']['strategies'])) {
                $this->raw['export']['strategies'] = [];
            }

            // Nested strategy interpretation must be done first, because once the parent data object
            // is created, getting children will trigger the internal data object lazy loading on
            // uninterpreted (and thus potentially invalid) data.
            foreach ($this->raw['export']['strategies'] as $key => $strategy) {

                if ( ! $strategy) {
                    continue;
                }

                if (true === $strategy) {
                    $strategy = [
                        'strategy' => $key,
                    ];
                }

                if ( ! is_array($this->raw['export']['strategies'][ $key ])) {
                    $this->raw['export']['strategies'][ $key ] = [
                        'strategy' => $key,
                    ];
                }

                $this->raw['export']['strategies'][ $key ]['columns'] = $this->normalizeStandardArrayProperty(
                    array_has($strategy, 'columns')
                        ?   array_get($strategy, 'columns', [])
                        :   array_get($strategy, 'fields', []),
                    'strategy',
                    ModelExportColumnData::class,
                    "export.strategies.{$key}.columns"
                );
            }


            $this->raw['export']['strategies'] = $this->normalizeStandardArrayProperty(
                $this->raw['export']['strategies'],
                'strategy',
                ModelExportStrategyData::class,
                'export.strategies'
            );
        }

        return $this;
    }

    /**
     * Normalizes an array with scope data.
     *
     * @param array  $scopes
     * @param string $parentKey
     * @return array
     */
    protected function normalizeScopeArray(array $scopes, $parentKey = 'list.scopes')
    {
        $scopes = $this->normalizeStandardArrayProperty(
            $scopes,
            'strategy',
            ModelScopeData::class,
            $parentKey
        );

        // Make sure that each scope entry has at least a method or a strategy
        foreach ($scopes as $key => &$value) {
            if ( ! $value['method'] && ! $value['strategy']) {
                $value['method'] = $key;
            }
        }

        unset($value);

        return $scopes;
    }

    /**
     * Normalizes a standard array property.
     *
     * @param array       $source
     * @param string      $standardProperty property to set for string values in normalized array
     * @param null|string $objectClass      dataobject FQN to interpret as
     * @param null|string $parentKey
     * @return array
     */
    protected function normalizeStandardArrayProperty(
        array $source,
        $standardProperty,
        $objectClass = null,
        $parentKey = null
    ) {
        $normalized = [];

        foreach ($source as $key => $value) {

            // list column may just set for order, defaults need to be filled in
            if (is_numeric($key) && ! is_array($value)) {
                $key    = $value;
                $value = [];
            }

            // if the value is 'true', the main property is assume to be the same as the key
            // (this is of limited use, but may make sense for export strategies)
            if (true === $value) {
                $value = $key;
            }

            // if value is just a string, it is the list strategy
            if (is_string($value)) {
                $value = [
                    $standardProperty => $value,
                ];
            }

            if ($objectClass) {

                if (empty($value)) {
                    $value = [];
                }

                $value = $this->makeClearedDataObject($objectClass, $value, $parentKey ? $parentKey . ".{$key}" : null);
            }

            $normalized[ $key ] = $value;
        }

        return $normalized;
    }

    /**
     * Makes a fresh dataobject with its defaults cleared before filling it with data.
     *
     * @param string      $objectClass
     * @param array       $data
     * @param null|string $parentKey
     * @return AbstractDataObject
     * @throws ModelConfigurationDataException
     */
    protected function makeClearedDataObject($objectClass, array $data, $parentKey = null)
    {
        /** @var AbstractDataObject $object */
        $object = new $objectClass();
        $object->clear();

        try {
            $object->setAttributes($data);

        } catch (ModelConfigurationDataException $e) {

            throw $e->setDotKey(
                $parentKey . ($e->getDotKey() ? '.' . $e->getDotKey() : null)
            );
        }

        return $object;
    }

}
