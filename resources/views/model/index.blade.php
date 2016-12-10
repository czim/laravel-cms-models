@extends(cms_config('views.layout'))

<?php $title = ucfirst($model->verbose_name_plural); ?>

@section('title', $title)

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li>
            <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
                {{ ucfirst(cms_trans('common.home')) }}
            </a>
        </li>
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

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

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

                <table class="table table-striped table-hover records-table">

                    <thead>
                        <tr>
                            @if ($model->list->activatable && cms_auth()->can("{$permissionPrefix}edit"))
                                <th class="column column-activate"></th>
                            @endif

                            @if ($model->list->orderable && cms_auth()->can("{$permissionPrefix}edit"))
                                <th class="column column-orderable">
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
                                $style = $model->list->activatable && ! $record->{$model->list->active_column}
                                       ? 'inactive' : null;
                            ?>

                            <tr class="records-row {{ $style }}">

                                @if ($model->list->activatable && cms_auth()->can("{$permissionPrefix}edit"))
                                    @include('cms-models::model.partials.list.column_activate', compact('model', 'record'))
                                @endif

                                @if ($model->list->orderable && cms_auth()->can("{$permissionPrefix}edit"))
                                    @include('cms-models::model.partials.list.column_orderable', [
                                        'model'         => $model,
                                        'record'        => $record,
                                        'isDraggable'   => $draggableForOrderable,
                                        'sortDirection' => $sortDirection
                                    ])
                                @endif

                                @foreach ($model->list->columns as $key => $column)
                                    @continue($column->hide)

                                    @include('cms-models::model.partials.list.column_strategy', [
                                        'key'      => $key,
                                        'column'   => $column,
                                        'strategy' => $listStrategies[ $key ],
                                        'model'    => $model,
                                        'record'   => $record,
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
        'routePrefix'
    ))

    @include('cms-models::model.partials.list.scripts_orderable', compact(
        'model',
        'sortColumn',
        'sortDirection',
        'routePrefix',
        'draggableForOrderable'
    ))
@endpush

