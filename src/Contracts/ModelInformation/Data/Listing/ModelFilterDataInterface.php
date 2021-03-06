<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Listing;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

interface ModelFilterDataInterface extends ArrayAccess, Arrayable
{

    /**
     * Returns friendly display label for the model.
     *
     * @return string
     */
    public function label();

    /**
     * Returns the source that the filter is made for (attribute or relationship).
     *
     * @return string
     */
    public function source();

    /**
     * Returns target column, relation, or other strategy to filter against.
     *
     * @return string
     */
    public function target();

    /**
     * Returns the filter strategy to apply for rendering & application.
     *
     * @return string
     */
    public function strategy();

    /**
     * Returns special options for the strategy.
     *
     * @return array
     */
    public function options();
    
    /**
     * @param ModelFilterDataInterface $with
     */
    public function merge(ModelFilterDataInterface $with);

}
