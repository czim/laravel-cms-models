<?php
namespace Czim\CmsModels\Strategies\Export\Column;

use Codesleeve\Stapler\Interfaces\Attachment;
use Illuminate\Database\Eloquent\Model;

class StaplerFileLinkStrategy extends DefaultStrategy
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
        $attachment = $this->resolveModelSource($model, $source);

        if ( ! ($attachment instanceof Attachment)) {
            return null;
        }

        /** @var Attachment $attachment */
        return $attachment->url();
    }

}
