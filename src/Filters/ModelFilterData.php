<?php
namespace Czim\CmsModels\Filters;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\Filter\FilterData;
use Illuminate\Contracts\Support\Arrayable;

class ModelFilterData extends FilterData
{

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array   associative
     */
    protected $defaults = [];

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $information;

    /**
     * Constructor: validate filter data
     *
     * @param ModelInformationInterface $information
     * @param array|Arrayable           $attributes
     * @param array|Arrayable           $defaults if provided, overrides internal defaults
     */
    public function __construct(ModelInformationInterface $information, $attributes, $defaults = null)
    {
        $this->information = $information;

        $this->initializeDefaultValues()
             ->initializeRules();

        parent::__construct($attributes, $defaults);
    }

    /**
     * Sets default values based on model information.
     *
     * @return $this
     */
    protected function initializeDefaultValues()
    {
        $defaults = [];

        $filters = $this->getFilterInformation();

        foreach (array_keys($filters) as $key) {
            $defaults[ $key ] = null;
        }

        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Sets validation rules based on model information.
     *
     * @return $this
     */
    protected function initializeRules()
    {
        $rules = [];

        $this->rules = $rules;

        return $this;
    }

    /**
     * Returns information about filters from the model information set.
     *
     * @return array|ModelFilterDataInterface[]|ModelListFilterData[]
     */
    protected function getFilterInformation()
    {
        return $this->information->list->filters ?: [];
    }

}
