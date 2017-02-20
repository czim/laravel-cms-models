<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsModels\Contracts\Data\ModelViewReferenceDataInterface;

/**
 * Class ModelViewReferenceData
 *
 * Information about a reference to a view partial that may be injected in various location.
 *
 * @property string   $view
 * @property string[] $variables
 */
class ModelViewReferenceData extends AbstractModelInformationDataObject implements ModelViewReferenceDataInterface
{

    protected $attributes = [

        // The full identifier for a view
        'view' => null,

        // A list of strings for variables that should be passed into the view
        'variables' => [],
    ];

    protected $known = [
        'view',
        'variables',
    ];

    /**
     * Returns the view identifier.
     *
     * @return string|null
     */
    public function view()
    {
        return $this->getAttribute('view');
    }

    /**
     * Returns names for variables to be passed into the view.
     *
     * @return string[]
     */
    public function variables()
    {
        return $this->getAttribute('variables') ?: [];
    }


    /**
     * @param ModelViewReferenceDataInterface|ModelViewReferenceData $with
     */
    public function merge(ModelViewReferenceDataInterface $with)
    {
        $this->mergeAttribute('view', $with->view);

        if ( ! empty($with->variables)) {
            $this->variables = array_unique(array_merge($this->variables, $with->variables));
        }
    }

}
