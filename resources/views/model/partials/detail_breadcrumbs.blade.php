
<ol class="breadcrumb">
    <li>
        <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
            {{ cms_trans('common.home') }}
        </a>
    </li>

    @if ($hasActiveListParent)

        @foreach ($listParents as $listParent)

            <li>
                @if (cms_auth()->can($listParent->permission_prefix . 'show'))
                    <a href="{{ cms_route($listParent->route_prefix . '.index') }}">
                        {{ ucfirst($listParent->information->verbose_name_plural) }}
                    </a>
                @else
                    {{ ucfirst($listParent->information->verbose_name_plural) }}
                @endif
            </li>

        @endforeach

        <?php /*
            // todo: consider whether this should be used or not
            <li>
                <a href="{{ cms_route("{$routePrefix}.index") }}?parent=">
                    {{ cms_trans('models.list-parents.all-models', [
                        'models' => ucfirst($model->verbose_name_plural)
                    ]) }}
                </a>
            </li>
        */ ?>

        <?php $listParent = last($listParents) ?>

        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ cms_trans('models.list-parents.children-for-parent-with-id', [
                    'children' => ucfirst($model->verbose_name_plural),
                    'parent'   => $listParent->information->verbose_name,
                    'id'       => $listParent->model->incrementing
                                    ?   '#' . $listParent->model->getKey()
                                    :   "'" . $listParent->model->getKey() . "'",
                ]) }}
            </a>
        </li>

    @elseif ( ! $model->single)

        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ ucfirst($model->verbose_name_plural) }}
            </a>
        </li>

    @endif

    <li class="active">
        {{ $title }}
    </li>
</ol>
