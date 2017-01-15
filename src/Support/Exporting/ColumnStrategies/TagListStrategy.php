<?php
namespace Czim\CmsModels\Support\Exporting\ColumnStrategies;

use Conner\Tagging\Taggable;
use Illuminate\Database\Eloquent\Model;

class TagListStrategy extends DefaultStrategy
{

    /**
     * Renders a display value to print to the export.
     *
     * @param Model|Taggable $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        return implode($this->getSeparator(), $model->tagNames());
    }

    /**
     * Returns separator between tags.
     *
     * @return string
     */
    protected function getSeparator()
    {
        return ',';
    }

}
