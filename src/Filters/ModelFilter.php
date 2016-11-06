<?php
namespace Czim\CmsModels\Filters;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\Filter\Contracts\FilterDataInterface;
use Czim\Filter\Filter;
use Illuminate\Contracts\Support\Arrayable;

class ModelFilter extends Filter
{

    /**
     * @var string
     */
    protected $filterDataClass = ModelFilterData::class;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $information;

    /**
     * @var array|ModelFilterDataInterface[]|ModelListFilterData[]
     */
    protected $filterInformation;


    /**
     * @param ModelInformationInterface           $information
     * @param array|Arrayable|FilterDataInterface $data
     */
    public function __construct(ModelInformationInterface $information, $data)
    {
        $this->information       = $information;
        $this->filterInformation = $this->getFilterInformation();

        parent::__construct($data);
    }


    /**
     * {@inheritdoc}
     */
    protected function applyParameter($parameterName, $parameterValue, $query)
    {
        if ( ! array_key_exists($parameterName, $this->filterInformation)) {
            parent::applyParameter($parameterName, $parameterValue, $query);
            return;
        }

        // We know about this filter, load up the relevant application strategy and apply it
        $information = $this->filterInformation[ $parameterName ];

        $this->getFilterStrategy()->apply(
            $query,
            $information->strategy(),
            $information->target(),
            $parameterValue
        );
    }

    /**
     * @return FilterStrategyInterface
     */
    protected function getFilterStrategy()
    {
        return app(FilterStrategyInterface::class);
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
