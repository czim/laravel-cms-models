<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Codesleeve\Stapler\Interfaces\Attachment;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class StaplerFile extends AbstractListDisplayStrategy
{
    const VIEW = 'cms-models::model.partials.list.strategies.stapler_file';

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed|Attachment $source     source column, method name or value
     * @return string|View
     */
    public function render(Model $model, $source)
    {
        $source = $this->resolveModelSource($model, $source);

        if ( ! ($source instanceof Attachment)) {
            throw new UnexpectedValueException("Stapler strategy expects Attachment as source");
        }

        return view(static::VIEW, [
            'exists'      => $source->size() > 0,
            'filename'    => $source->originalFilename(),
            'url'         => $source->url(),
        ]);
    }

}
