<?php
namespace Czim\CmsModels\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Repositories\Collectors\ModelInformationInterpreterInterface;
use Czim\CmsModels\Support\Data\ModelInformation;

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
                'strategy'
            );

            $this->raw['list']['filters'] = $this->normalizeStandardArrayProperty(
                array_get($this->raw['list'], 'filters', []),
                'strategy'
            );
        }

        return $this;
    }

    /**
     * Normalizes a standard array property.
     *
     * @param array  $source
     * @param string $standardProperty property to set for string values in normalized array
     * @return array
     */
    protected function normalizeStandardArrayProperty(array $source, $standardProperty)
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

            $normalized[ $key ] = $value;
        }

        return $normalized;
    }

}
