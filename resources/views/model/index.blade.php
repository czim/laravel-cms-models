@extends(cms_config('views.layout'))

<?php
    if ($hasActiveListParent) {
        if ($listParent = last($listParents)) {
            $title = cms_trans('models.list-parents.children-for-parent-with-id', [
                'children' => ucfirst($model->labelPlural()),
                'parent'   => $listParent->information->label(),
                'id'       => $listParent->model->incrementing
                    ?   '#' . $listParent->model->getKey()
                    :   "'" . $listParent->model->getKey() . "'",
            ]);
        } else {
            $title = ucfirst($model->labelPlural());
        }

    } else {
        $title = ucfirst($model->labelPlural());
    }
?>

@section('title', $title)

@section('breadcrumbs')
    @include('cms-models::model.partials.index_breadcrumbs', compact(
        'model',
        'routePrefix',
        'title',
        'hasActiveListParent',
        'topListParentOnly',
        'listParents'
    ))
@endsection


@section('content')

    <div class="page-header">

        <div class="btn-toolbar pull-right">

            @include('cms-models::model.partials.index_parent_back_button', compact(
                'model',
                'listParents',
                'hasActiveListParent',
                'topListParentOnly'
            ))

            <div class="btn-group">
                @if (cms_auth()->can("{$permissionPrefix}create"))
                    <a href="{{ cms_route("{$routePrefix}.create") }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> &nbsp;
                        {{ cms_trans('models.button.new-record', [ 'name' => $model->label() ]) }}
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

            @if ( ! $hasActiveListParent && ! $model->list->disable_scopes && $model->list->scopes && count($model->list->scopes))
                @include('cms-models::model.partials.list.scopes', [
                    'model'       => $model,
                    'totalCount'  => $totalCount,
                    'scopes'      => $model->list->scopes,
                    'scopeCounts' => $scopeCounts,
                ])
            @endif

            @include('cms-models::model.partials.list.filters', compact('model', 'filters', 'filterStrategies'))

            @if (count($records))

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

                            <th class="column column-actions"></th>
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


                                $defaultActionUrl = $defaultRowAction ? $defaultRowAction->link($record) : false;
                            ?>

                            <tr class="records-row {{ $style }}" @if ($defaultActionUrl) default-action-url="{{ $defaultActionUrl }}" @endif>

                                @if ($model->list->activatable)
                                    @include('cms-models::model.partials.list.column_activate', compact('model', 'record', 'permissionPrefix'))
                                @endif

                                @if ($model->list->orderable)
                                    @include('cms-models::model.partials.list.column_orderable', [
                                        'model'            => $model,
                                        'record'           => $record,
                                        'isDraggable'      => $draggableOrderable,
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
                                        'hasDefaultAction' => (bool) $defaultActionUrl,
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
                        {{ cms_trans('models.no-records-found', [ 'name' => $model->labelPlural() ]) }}
                    </em>
                </div>

            @endif

        </div>
    </div>

    @if (count($availableExportKeys))
        @include('cms-models::model.partials.list.export_buttons', compact(
            'model',
            'routePrefix',
            'availableExportKeys'
        ))
    @endif


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
            'draggableOrderable'
        ))
    @endif

    @if (count($records) && $defaultRowAction)
        <script>
            $(function() {
                $('tr.records-row td.default-action').click(function () {
                    window.location.href = $(this).closest('tr').attr('default-action-url');
                });
            });
        </script>
    @endif

@endpush

