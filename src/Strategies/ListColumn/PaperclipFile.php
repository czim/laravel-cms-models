<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\Paperclip\Contracts\AttachmentInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class PaperclipFile extends AbstractListDisplayStrategy
{
    const VIEW = 'cms-models::model.partials.list.strategies.paperclip_file';

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed|AttachmentInterface $source     source column, method name or value
     * @return string|View
     */
    public function render(Model $model, $source)
    {
        $source = $this->resolveModelSource($model, $source);

        if ( ! ($source instanceof AttachmentInterface)) {
            throw new UnexpectedValueException("Paperclip strategy expects Attachment as source");
        }

        return view(static::VIEW, [
            'exists'      => $source->size() > 0,
            'filename'    => $source->originalFilename(),
            'url'         => $source->url(),
        ]);
    }

}
