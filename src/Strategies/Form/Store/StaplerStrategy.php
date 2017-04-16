<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Illuminate\Database\Eloquent\Model;

class StaplerStrategy extends DefaultStrategy
{

    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        // Normalize to an array if required
        if ( ! is_array($value)) {
            $value = [
                'keep'   => 0,
                'upload' => $value,
            ];
        }

        // If the value is empty, use the stapler null value instead
        if (empty($value['upload'])) {
            // @codeCoverageIgnoreStart
            $value['upload'] = STAPLER_NULL;
            // @codeCoverageIgnoreEnd
        }

        return $value;
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        // If the keep flag is set, we don't touch the model
        if (array_get($value, 'keep')) {
            return;
        }

        $model->{$source} = array_get($value, 'upload');
    }

}
