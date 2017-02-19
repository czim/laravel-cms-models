<?php
namespace Czim\CmsModels\View\ListStrategies;

use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\Model;

class TagList extends AbstractListDisplayStrategy
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model|Taggable $model
     * @param mixed          $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        return implode($this->getSeparator(), array_map('e', $model->tagNames()));
    }

    /**
     * Returns separator to concatenate the tags by.
     *
     * @return string
     */
    protected function getSeparator()
    {
        return ', ';
    }

}
