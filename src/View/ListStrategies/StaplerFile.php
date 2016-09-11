<?php
namespace Czim\CmsModels\View\ListStrategies;

use Codesleeve\Stapler\Interfaces\Attachment;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class StaplerFile extends AbstractListDisplayStrategy
{

    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed|Attachment $source     source column, method name or value
     * @return string
     */
    public function render(Model $model, $source)
    {
        if ( ! ($source instanceof Attachment)) {
            throw new UnexpectedValueException("Stapler strategy expects Attachment as source");
        }

        return view('cms-models::model.partials.list.strategies.stapler_file', [
            'filename'    => $source->originalFilename(),
            'url'         => $source->url(),
        ])->render();
    }

}
