<?php
namespace Czim\CmsModels\Support\Data;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\CmsModels\Contracts\Data\ModelActionReferenceDataInterface;

/**
 * Class ModelActionReferenceData
 *
 * Information about a route action that may be performed, usually for a click.
 *
 * @property string               $type
 * @property string|string[]|null $permissions
 * @property string|null          $route
 * @property string[]             $variables
 */
class ModelActionReferenceData extends AbstractDataObject implements ModelActionReferenceDataInterface
{

    protected $attributes = [

        // A special type, defaults to using 'route' if none specified
        // Available types: edit, show
        'type' => null,

        // The permission(s) required to use this action. May be a string or an array.
        // If more are given, all must match.
        'permissions' => null,

        // A route, used if no type is set
        'route' => null,

        // A list of strings for variables that should be used as arguments for the route
        'variables' => [],
    ];


    /**
     * Returns the special type identifier.
     *
     * @return string|null
     */
    public function type()
    {
        return $this->getAttribute('type');
    }

    /**
     * Returns the route name.
     *
     * @return string|null
     */
    public function route()
    {
        return $this->getAttribute('route');
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
     * Returns required permissions for this action.
     *
     * @return string[]
     */
    public function permissions()
    {
        $permissions = $this->getAttribute('permissions') ?: [];

        if ( ! is_array($permissions)) {
            $permissions = [ $permissions ];
        }

        return $permissions;
    }


    /**
     * @param ModelActionReferenceDataInterface|ModelActionReferenceData $with
     */
    public function merge(ModelActionReferenceDataInterface $with)
    {
        $this->mergeAttribute('type', $with->type);
        $this->mergeAttribute('route', $with->route);

        if ( ! empty($with->variables)) {
            $this->variables = array_unique(array_merge($this->variables, $with->variables));
        }
    }

}
