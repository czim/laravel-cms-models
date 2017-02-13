
<ol class="breadcrumb">
    <li>
        <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
            {{ ucfirst(cms_trans('common.home')) }}
        </a>
    </li>

    @if ($hasActiveListParent)

        <?php
            $previousListParent = null;
        ?>

        @foreach ($listParents as $listParent)

            <li>
                @if (cms_auth()->can($listParent->permission_prefix . 'show'))
                    <a href="{{ cms_route($listParent->route_prefix . '.index') }}{{ null !== $listParent->query ? '?' . $listParent->query : null }}">
                @endif

                    @if ($previousListParent)

                        {{ cms_trans('models.list-parents.children-for-parent-with-id', [
                            'children' => ucfirst($listParent->information->labelPlural()),
                            'parent'   => $previousListParent->information->label(),
                            'id'       => $previousListParent->model->incrementing
                                            ?   '#' . $previousListParent->model->getKey()
                                            :   "'" . $previousListParent->model->getKey() . "'",
                        ]) }}

                    @else
                        {{ ucfirst($listParent->information->labelPlural()) }}
                    @endif

                @if (cms_auth()->can($listParent->permission_prefix . 'show'))
                    </a>
                @endif
            </li>

            <?php
                $previousListParent = $listParent;
            ?>

        @endforeach

        <?php $listParent = last($listParents) ?>

        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ cms_trans('models.list-parents.children-for-parent-with-id', [
                    'children' => ucfirst($model->labelPlural()),
                    'parent'   => $listParent->information->label(),
                    'id'       => $listParent->model->incrementing
                                    ?   '#' . $listParent->model->getKey()
                                    :   "'" . $listParent->model->getKey() . "'",
                ]) }}
            </a>
        </li>

    @elseif ( ! $model->single)

        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ ucfirst($model->labelPlural()) }}
            </a>
        </li>

    @endif

    <li class="active">
        {{ $title }}
    </li>
</ol>
