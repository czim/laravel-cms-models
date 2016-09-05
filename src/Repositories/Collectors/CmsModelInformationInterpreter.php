<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationInterpreterInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListColumnData;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Data\ModelScopeData;

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

        $this->interpretListData();

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

            $this->raw['list']['columns'] = $this->normalizeStandardArrayProperty(
                array_get($this->raw['list'], 'columns', []),
                'strategy',
                ModelListColumnData::class
            );


            $filters = array_get($this->raw['list'], 'filters', []);
            if (false === $filters) {
                $this->raw['list']['disable_filters'] = true;
            } else {
                $this->raw['list']['filters'] = $this->normalizeStandardArrayProperty(
                    $filters,
                    'strategy',
                    ModelListFilterData::class
                );
            }

            $scopes = array_get($this->raw['list'], 'scopes', []);
            if (false === $scopes) {
                $this->raw['list']['disable_scopes'] = true;
            } else {
                $this->raw['list']['scopes'] = $this->normalizeScopeArray($scopes);
            }
        }

        return $this;
    }

    /**
     * Normalizes an array with scope data.
     *
     * @param array $scopes
     * @return array
     */
    protected function normalizeScopeArray(array $scopes)
    {
        $scopes = $this->normalizeStandardArrayProperty(
            $scopes,
            'strategy',
            ModelScopeData::class
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
     * @param string      $standardProperty     property to set for string values in normalized array
     * @param null|string $objectClass          dataobject FQN to interpret as
     * @return array
     */
    protected function normalizeStandardArrayProperty(array $source, $standardProperty, $objectClass = null)
    {
        $normalized = [];

        foreach ($source as $key => $value) {

            // list column may just set for order, defaults need to be filled in
            if (is_numeric($key)) {
                $key    = $value;
                $value = [];
            }

            // if value is just a string, it is the list strategy
            if (is_string($value)) {
                $value = [
                    $standardProperty => $value,
                ];
            }

            if ($objectClass) {
                $value = $this->makeClearedDataObject($objectClass, $value);
            }

            $normalized[ $key ] = $value;
        }

        return $normalized;
    }

    /**
     * Makes a fresh dataobject with its defaults cleared before filling it with data.
     *
     * @param string $objectClass
     * @param array  $data
     * @return AbstractDataObject
     */
    protected function makeClearedDataObject($objectClass, array $data)
    {
        /** @var AbstractDataObject $object */
        $object = new $objectClass();
        $object->clear();

        $object->setAttributes($data);

        return $object;
    }

}
