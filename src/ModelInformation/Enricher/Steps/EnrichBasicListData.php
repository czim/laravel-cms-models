<?php
namespace Czim\CmsModels\ModelInformation\Enricher\Steps;

use Czim\CmsModels\Contracts\Routing\RouteHelperInterface;
use Czim\CmsModels\Support\Enums\ActionReferenceType;

class EnrichBasicListData extends AbstractEnricherStep
{

    /**
     * Performs enrichment.
     */
    protected function performEnrichment()
    {
        $this->setReferenceSource()
             ->setSortingOrder()
             ->setDefaultRowActions();
    }

    /**
     * Sets default sorting order, if empty.
     *
     * @return $this
     */
    protected function setSortingOrder()
    {
        if (null !== $this->info->list->default_sort) {
            return $this;
        }

        if ($this->info->list->orderable && $this->info->list->getOrderableColumn()) {
            $this->info->list->default_sort = $this->info->list->getOrderableColumn();
        } elseif ($this->info->timestamps) {
            $this->info->list->default_sort = $this->info->timestamp_created;
        } elseif ($this->info->incrementing) {
            $this->info->list->default_sort = $this->model->getKeyName();
        }

        return $this;
    }

    /**
     * Sets default reference source, better than primary key, if possible.
     *
     * @return $this
     */
    protected function setReferenceSource()
    {
        if (null !== $this->info->reference->source) {
            return $this;
        }

        // No source is set, see if we can find a standard match
        $matchAttributes = config('cms-models.analyzer.reference.sources', []);
        
        foreach ($matchAttributes as $matchAttribute) {

            if (array_key_exists($matchAttribute, $this->info->attributes)) {
                $this->info->reference->source = $matchAttribute;
                break;
            }
        }

        return $this;
    }

    /**
     * Sets default actions, if configured to and none are defined.
     *
     * @return $this
     */
    protected function setDefaultRowActions()
    {
        $actions = $this->info->list->default_action ?: [];

        if (count($actions)) {
            return $this;
        }

        $addEditAction = config('cms-models.defaults.default-listing-action-edit', false);
        $addShowAction = config('cms-models.defaults.default-listing-action-show', false);

        if ( ! $addEditAction && ! $addShowAction) {
            return $this;
        }

        // The edit and/or show action should be set as default index row click action.

        $modelSlug        = $this->getRouteHelper()->getRouteSlugForModelClass(get_class($this->model));
        $permissionPrefix = $this->getRouteHelper()->getPermissionPrefixForModelSlug($modelSlug);

        if ($addEditAction) {
            $actions[] = [
                'strategy'    => ActionReferenceType::EDIT,
                'permissions' => "{$permissionPrefix}edit",
            ];
        }

        if ($addShowAction) {
            $actions[] = [
                'strategy'    => ActionReferenceType::SHOW,
                'permissions' => "{$permissionPrefix}show",
            ];
        }

        $this->info->list->default_action = $actions;

        return $this;
    }

    /**
     * @return RouteHelperInterface
     */
    protected function getRouteHelper()
    {
        return app(RouteHelperInterface::class);
    }

}
