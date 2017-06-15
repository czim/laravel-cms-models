<?php
namespace Czim\CmsModels\Filters;

use Czim\CmsModels\Contracts\ModelInformation\Data\Listing\ModelFilterDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Support\Factories\FilterStrategyFactoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
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
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        // We know about this filter, load up the relevant application strategy and apply it
        $information = $this->filterInformation[ $parameterName ];

        $filter = $this->getFilterFactory()->make(
            $information->strategy(),
            $parameterName,
            $this->filterInformation[ $parameterName ]
        );

        $filter->apply($query, $information->target(), $parameterValue);
    }

    /**
     * Altered to allow '0' to be a usable value, but ignore empty string.
     *
     * {@inheritdoc}
     */
    protected function isParameterValueUnset($parameter, $value)
    {
        return $value === '' || $value === null;
    }

    /**
     * @return FilterStrategyFactoryInterface
     */
    protected function getFilterFactory()
    {
        return app(FilterStrategyFactoryInterface::class);
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
