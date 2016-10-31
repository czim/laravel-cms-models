<?php
namespace Czim\CmsModels\Support\Data\Strategies;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\Strategies\ModelMetaReferenceInterface;

/**
 * Class ModelMetaReference
 *
 * @property string $model              the model to reference/search
 * @property string $strategy           the reference strategy to use
 * @property string $context_strategy   an optional extra context strategy to apply to the query builder
 * @property string $source             the source to display for a reference
 * @property string $target             the target(s) to use for any reference search
 * @property array  $parameters         optional parameters for the strategies
 * @property string $sort_direction     the sort direction to use when sorting references
 */
class ModelMetaReference extends AbstractDataObject implements ModelMetaReferenceInterface
{

    protected $attributes = [
        'model'            => null,
        'strategy'         => null,
        'context_strategy' => null,
        'source'           => null,
        'target'           => null,
        'parameters'       => [],
        'sort_direction'   => null,
    ];

    /**
     * Returns the model to get reference(s) for.
     *
     * @return string
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Returns the reference strategy to use.
     *
     * @return string
     */
    public function strategy()
    {
        return $this->strategy;
    }

    /**
     * Returns an optional extra context strategy to apply to the query builder.
     *
     * @return string
     */
    public function contextStrategy()
    {
        return $this->context_strategy;
    }

    /**
     * Returns the source to display for a reference.
     *
     * @return string
     */
    public function source()
    {
        return $this->source;
    }

    /**
     * Returns the target(s) to use for any reference search.
     *
     * @return string
     */
    public function target()
    {
        return $this->target ?: $this->source;
    }

    /**
     * Returns optional parameters for the strategies.
     *
     * @return array
     */
    public function parameters()
    {
        return $this->parameters ?: [];
    }

    /**
     * Returns the sorting direction to use when sorting references.
     *
     * @return string 'asc' or 'desc'
     */
    public function sortDirection()
    {
        return $this->sort_direction === 'desc' ? 'desc' : 'asc';
    }

}
