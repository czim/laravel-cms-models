<?php
namespace Czim\CmsModels\Strategies\Export\Column;

use Czim\Paperclip\Contracts\AttachmentInterface;
use Illuminate\Database\Eloquent\Model;

class PaperclipFileLinkStrategy extends DefaultStrategy
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

        if ( ! ($attachment instanceof AttachmentInterface)) {
            return null;
        }

        return $attachment->url();
    }

}
