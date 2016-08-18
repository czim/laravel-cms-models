<?php
namespace Czim\CmsModels\View\ListStrategies;

use Illuminate\Database\Eloquent\Model;

class Checkbox extends AbstractListDisplayStrategy
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model  $model
     * @param string $source source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        if ($this->interpretAsBoolean($model->{$source})) {
            return '<i class="fa fa-check text-success"></i>';
        }

        return '<i class="fa fa-times text-danger"></i>';
    }

    /**
     * Parses a source value as a boolean value.
     *
     * @param mixed $value
     * @return bool
     */
    protected function interpretAsBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value != 0;
        }

        if (is_string($value)) {

            $value = trim($value);

            if ('' === $value || preg_match('#^n|no|f|false|nee|off|disabled|inactive&#', trim($value))) {
                return false;
            }

            return true;
        }

        return (bool) $value;
    }

}
