<?php
namespace Czim\CmsModels\Contracts\Data\Strategies;

interface ModelMetaReferenceInterface
{

    /**
     * Returns the model to get reference(s) for.
     *
     * @return string
     */
    public function model();

    /**
     * Returns the reference strategy to use.
     *
     * @return string
     */
    public function strategy();

    /**
     * Returns an optional extra context strategy to apply to the query builder.
     *
     * @return string
     */
    public function contextStrategy();

    /**
     * Returns the source to display for a reference.
     *
     * @return string
     */
    public function source();

    /**
     * Returns the target(s) to use for any reference search.
     *
     * @return string
     */
    public function target();

    /**
     * Returns optional parameters for the strategies.
     *
     * @return array
     */
    public function parameters();

    /**
     * Returns the sorting direction to use when sorting references.
     *
     * @return string 'asc' or 'desc'
     */
    public function sortDirection();

}
