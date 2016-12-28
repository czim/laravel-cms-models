@extends(cms_config('views.layout'))

<?php
    if ($hasActiveListParent) {
        if ($listParent = last($listParents)) {
            $title = cms_trans('models.list-parents.children-for-parent-with-id', [
                'children' => ucfirst($model->verbose_name_plural),
                'parent'   => $listParent->information->verbose_name,
                'id'       => $listParent->model->incrementing
                    ?   '#' . $listParent->model->getKey()
                    :   "'" . $listParent->model->getKey() . "'",
            ]);
        } else {
            $title = ucfirst($model->verbose_name_plural);
        }

    } else {
        $title = ucfirst($model->verbose_name_plural);
    }
?>

@section('title', $title)

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li>
            <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
                {{ ucfirst(cms_trans('common.home')) }}
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

        @endif

        <li class="active">
            {{ $title }}
        </li>
    </ol>
@endsection


@section('content')

    <?php
        $currentCount = method_exists($records, 'total') ? $records->total() : 0;

        // if the list is sorted by the orderable column and all records
        // are visible, allow drag & drop ordering.
        $draggableForOrderable = $model->list->getOrderableColumn() === $sortColumn
            &&  (   $totalCount == $currentCount
                ||  (! $activeScope  && ! count($filters))
                );
    ?>

    <div class="page-header">

        <div class="btn-toolbar pull-right">

            @if ($hasActiveListParent)
                <div class="btn-group">
                    <a href="{{ cms_route("{$routePrefix}.index") }}?parent=" class="btn btn-default">
                        {{ cms_trans('models.list-parents.back-to-all-models', [
                            'models' => ucfirst($model->verbose_name_plural)
                        ]) }}
                    </a>
                </div>
            @endif

            <div class="btn-group">
                @if (cms_auth()->can("{$permissionPrefix}create"))
                    <a href="{{ cms_route("{$routePrefix}.create") }}" class="btn btn-primary">
                        {{ cms_trans('models.button.new-record', [ 'name' => $model->verbose_name ]) }}
                    </a>
                @endif
            </div>
        </div>

        <h1>{{ $title }}</h1>
    </div>


    {{-- Before view --}}
    @if ($model->list->before && $model->list->before->view)
        @include($model->list->before->view, $model->list->before->variables())
    @endif


    <div class="row">
        <div>

            @if ( ! $model->list->disable_scopes && $model->list->scopes && count($model->list->scopes))
                @include('cms-models::model.partials.list.scopes', [
                    'model'       => $model,
                    'totalCount'  => $totalCount,
                    'scopes'      => $model->list->scopes,
                    'scopeCounts' => $scopeCounts,
                ])
            @endif

            @include('cms-models::model.partials.list.filters', compact('model', 'filters', 'filterStrategies'))

            @if (count($records))

                <?php
                    // Prepare the default action for clicking table rows
                    /** @var \Czim\CmsModels\Contracts\Data\ModelActionReferenceDataInterface $defaultAction */
                    $defaultAction = $model->list->getDefaultAction();

                    if ($defaultAction) {

                        // Interpret special types as localized routes
                        if ($defaultAction->type()) {

                            switch ($defaultAction->type()) {
                                case \Czim\CmsModels\Support\Enums\ActionReferenceType::SHOW:
                                    $defaultAction->route       = "{$routePrefix}.show";
                                    $defaultAction->permissions = [ "{$permissionPrefix}show" ];
                                    $defaultAction->variables   = [ 'recordKey' ];
                                    break;

                                case \Czim\CmsModels\Support\Enums\ActionReferenceType::EDIT:
                                    $defaultAction->route       = "{$routePrefix}.edit";
                                    $defaultAction->permissions = [ "{$permissionPrefix}edit" ];
                                    $defaultAction->variables   = [ 'recordKey' ];
                                    break;

                                default:
                                    $defaultAction = null;
                            }

                        }

                        if ( ! $defaultAction->route()) {
                            $defaultAction = null;
                        }
                    }
                ?>

                <table class="table table-striped table-hover records-table">

                    <thead>
                        <tr>
                            @if ($model->list->activatable)
                                <th class="column column-activate"></th>
                            @endif

                            @if ($model->list->orderable)
                                <th class="column column-orderable column-right">
                                    @include('cms-models::model.partials.list.column_header', [
                                        'sortKey'       => $model->list->order_column ?: 'position',
                                        'label'         => ucfirst(cms_trans('models.orderable.position')),
                                        'sortable'      => true,
                                        'active'        => $model->list->getOrderableColumn() === $sortColumn,
                                        'sortDirection' => $sortDirection,
                                    ])
                                </th>
                            @endif

                            @foreach ($model->list->columns as $key => $column)
                                @continue($column->hide)

                                <th class="column {{ $column->style }}">
                                    @include('cms-models::model.partials.list.column_header', [
                                        'label'         => $column->header(),
                                        'sortKey'       => $key,
                                        'sortable'      => $column->sortable,
                                        'active'        => $key === $sortColumn,
                                        'sortDirection' => $sortDirection,
                                    ])
                                </th>

                            @endforeach

                            @if (cms_auth()->can(["{$permissionPrefix}edit", "{$permissionPrefix}delete"]))
                                <th class="column column-actions"></th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                            // set the user link route according to permissions
                            $route = cms_auth()->can("{$permissionPrefix}edit") ? "{$routePrefix}.edit" : "{$routePrefix}.show";
                        ?>

                        @foreach ($records as $record)

                            <?php
                                $recordKey = $record->getKey();

                                $style = $model->list->activatable && ! $record->{$model->list->active_column}
                                       ? 'inactive' : null;

                                $defaultActionUrl = null;
                                if ($defaultAction) {

                                    $resolvedVariables = [];
                                    foreach ($defaultAction->variables() as $variable) {
                                        $resolvedVariables[] = isset(${$variable}) ? ${$variable} : null;
                                    }
                                    $defaultActionUrl = route($defaultAction->route(), $resolvedVariables);
                                }
                            ?>

                            <tr class="records-row {{ $style }}" default-action-url="{{ $defaultActionUrl }}">

                                @if ($model->list->activatable)
                                    @include('cms-models::model.partials.list.column_activate', compact('model', 'record', 'permissionPrefix'))
                                @endif

                                @if ($model->list->orderable)
                                    @include('cms-models::model.partials.list.column_orderable', [
                                        'model'            => $model,
                                        'record'           => $record,
                                        'isDraggable'      => $draggableForOrderable,
                                        'sortDirection'    => $sortDirection,
                                        'permissionPrefix' => $permissionPrefix,
                                    ])
                                @endif

                                @foreach ($model->list->columns as $key => $column)
                                    @continue($column->hide)

                                    @include('cms-models::model.partials.list.column_strategy', [
                                        'key'              => $key,
                                        'column'           => $column,
                                        'strategy'         => $listStrategies[ $key ],
                                        'model'            => $model,
                                        'record'           => $record,
                                        'hasDefaultAction' => (bool) $defaultAction,
                                    ])
                                @endforeach

                                @include('cms-models::model.partials.list.column_actions', compact(
                                    'record',
                                    'permissionPrefix',
                                    'route'
                                ))
                            </tr>

                        @endforeach

                    </tbody>
                </table>

                <div class="listing-footer clearfix">

                    @if (method_exists($records, 'links'))
                        @include('cms-models::model.partials.list.pagination', compact(
                            'records',
                            'pageSize',
                            'pageSizeOptions'
                        ))
                    @endif

                    @if ($totalCount)

                        <div class="well well-sm listing-counts pull-right">
                            @include('cms-models::model.partials.list.counts', [
                                'total'   => $totalCount,
                                'current' => $currentCount,
                            ])
                        </div>

                    @endif

                </div>

            @else

                <div>
                    <em>
                        {{ cms_trans('models.no-records-found', [ 'name' => $model->verbose_name_plural ]) }}
                    </em>
                </div>

            @endif

        </div>
    </div>


    {{-- After view --}}
    @if ($model->list->after && $model->list->after->view)
        @include($model->list->after->view, $model->list->after->variables())
    @endif


    @if ($model->allowDelete())
        @include('cms-models::model.partials.list.modal_delete', compact('model', 'routePrefix'))
    @endif

    @if ($model->list->orderable)
        @include('cms-models::model.partials.list.modal_orderable', compact('model', 'routePrefix'))
    @endif

@endsection


@push('javascript-end')

    @include('cms-models::model.partials.list.scripts_delete', compact(
        'model',
        'routePrefix'
    ))

    @include('cms-models::model.partials.list.scripts_activatable', compact(
        'model',
        'routePrefix',
        'permissionPrefix'
    ))

    @if (cms_auth()->can("{$permissionPrefix}edit"))
        @include('cms-models::model.partials.list.scripts_orderable', compact(
            'model',
            'sortColumn',
            'sortDirection',
            'routePrefix',
            'draggableForOrderable'
        ))
    @endif

    @if (count($records) && $defaultAction)
        <script>
            $(function() {
                $('tr.records-row td.default-action').click(function () {
                    window.location.href = $(this).closest('tr').attr('default-action-url');
                });
            });
        </script>
    @endif

@endpush

