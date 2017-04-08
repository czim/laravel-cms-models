<?php
namespace Czim\CmsModels\Strategies\Action;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use UnexpectedValueException;

class ChildrenStrategy extends AbstractActionStrategy
{
    const ROUTE_POSTFIX = '.index';

    /**
     * Whether the action is considered permissible.
     *
     * @var bool
     */
    protected $isPermitted = false;

    /**
     * The prefix to use for making routes.
     *
     * @var string
     */
    protected $routePrefix;

    /**
     * Class name of related model that should be linked to.
     *
     * @var string
     */
    protected $otherModelClass;

    /**
     * Relation method name to use in the parent parameter;
     *
     * @var string
     */
    protected $relation;

    /**
     * Whether the relation is of the MorphTo type.
     *
     * @var bool
     */
    protected $isMorphTo;


    /**
     * Performs initialization.
     * Override this to customize strategy implementations.
     */
    protected function performInit()
    {
        $this->prepareTargetRoute()
             ->prepareParentParameter()
             ->checkForMorphToRelation();


        // Check permissions
        $permissions = $this->actionData->permissions();

        if (empty($permissions)) {
            $this->isPermitted = true;
        } else {
            $this->isPermitted = cms_auth()->can($permissions);
        }
    }

    /**
     * Returns the action link for a given model instance.
     *
     * @param Model $model
     * @return string|false
     */
    public function link(Model $model)
    {
        if ( ! $this->routePrefix || ! $this->isPermitted) {
            return false;
        }

        // If the relation is morphTo, the key needs to be prepared with type:key identicator
        if ($this->isMorphTo) {
            $parentIndicator = $this->getMorphTypeForModel($this->modelClass) . ':' . $model->getKey();
        } else {
            $parentIndicator = $model->getKey();
        }

        return route($this->routePrefix . static::ROUTE_POSTFIX)
             . '?parent=' . $this->relation . ':' . $parentIndicator;
    }

    /**
     * @return $this
     */
    protected function prepareTargetRoute()
    {
        $this->getOtherModelClass();

        /** @var RouteHelperInterface $routeHelper */
        $routeHelper = app(RouteHelperInterface::class);

        $this->routePrefix = $routeHelper->getRouteNameForModelClass($this->otherModelClass, true);

        return $this;
    }

    /**
     * @return $this
     */
    protected function prepareParentParameter()
    {
        $this->relation = $this->getRelationName();

        if ( ! $this->relation) {
            throw new UnexpectedValueException(
                "Could not determine relation for children link action for model {$this->otherModelClass}"
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function checkForMorphToRelation()
    {
        $relationInstance = (new $this->otherModelClass)->{$this->relation}();

        if ( ! ($relationInstance instanceof Relation)) {
            throw new UnexpectedValueException(
                "Method '{$this->relation}' on model '{$this->otherModelClass}' is not a relation"
            );
        }

        $this->isMorphTo = $relationInstance instanceof MorphTo;

        return $this;
    }

    /**
     * Returns the model FQN for the target model for the link.
     *
     * If the relationship is MorphTo, we cannot know the model class in every case.
     *
     * @return string
     */
    protected function getOtherModelClass()
    {
        $this->otherModelClass = array_get($this->actionData->options(), 'model');

        if ( ! $this->otherModelClass || ! is_a($this->otherModelClass, Model::class, true)) {
            throw new UnexpectedValueException("{$this->otherModelClass} is not a valid target model class");
        }

        /** @var ModelInformationRepositoryInterface $infoRepository */
        $infoRepository = app(ModelInformationRepositoryInterface::class);

        $info = $infoRepository->getByModelClass($this->otherModelClass);

        if ( ! $info) {
            throw new UnexpectedValueException("{$this->otherModelClass} is not a CMS model");
        }

        return $this->otherModelClass;
    }

    /**
     * Returns configured relation name for children link.
     *
     * Falls back to list.parents relation name if exactly one given.
     *
     * @return string|false
     */
    protected function getRelationName()
    {
        $relation = array_get($this->actionData->options(), 'relation');

        if ($relation) {
            return $relation;
        }

        /** @var ModelInformationRepositoryInterface $infoRepository */
        $infoRepository = app(ModelInformationRepositoryInterface::class);

        $info = $infoRepository->getByModelClass($this->otherModelClass);

        if ( ! $info || empty($info->list->parents) || count($info->list->parents) !== 1) {
            return false;
        }

        return head($info->list->parents)->relation;
    }

    /**
     * @param string $modelClass
     * @return string
     */
    protected function getMorphTypeForModel($modelClass)
    {
        $map = Relation::morphMap();

        $class = ltrim($modelClass, '\\');

        if (empty($map)) {
            return $class;
        }

        if (false !== ($type = array_search($class, $map))) {
            return $type;
        }

        return $class;
    }

}
