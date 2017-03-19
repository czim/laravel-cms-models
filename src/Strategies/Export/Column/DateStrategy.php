<?php
namespace Czim\CmsModels\Strategies\Export\Column;

use Illuminate\Database\Eloquent\Model;

class DateStrategy extends DefaultStrategy
{

    /**
     * Renders a display value to print to the export.
     *
     * @param Model $model
     * @param mixed $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        $value = $this->resolveModelSource($model, $source);

        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value->format($this->getFormat());
        }

        return $value;
    }

    /**
     * @return string
     */
    protected function getFormat()
    {
        if ($format = array_get($this->exportColumnData->options(), 'format')) {
            return $format;
        }

        return 'Y-m-d H:i:s';
    }
}
