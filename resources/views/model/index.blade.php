@extends(cms_config('views.layout'))

@section('title', $model->verbose_name_plural)


@section('breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">Home</a></li>
        <li class="active">{{ $model->verbose_name_plural }}</li>
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

        <h1>{{ $model->verbose_name_plural }}</h1>
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

            @include('cms-models::model.partials.list.filters', [
                'model'   => $model,
                'filters' => $filters,
            ])

            @if (count($records))

                <table class="table table-striped table-hover records-table">

                    <thead>
                        <tr>
                            @if ($model->list->activatable)
                                <th class="column column-activate"></th>
                            @endif

                            @if ($model->list->orderable)
                                <th class="column column-orderable">
                                    @include('cms-models::model.partials.list.column_header_sort', [
                                        'sortKey'   => $model->list->order_column ?: 'position',
                                        'label'     => ucfirst(cms_trans('models.orderable.position')),
                                        'active'    => $model->list->getOrderableColumn() === $sortColumn,
                                        'direction' => $sortDirection,
                                    ])
                                </th>
                            @endif

                            @foreach ($model->list->columns as $key => $column)
                                @continue($column->hide)

                                <th class="column {{ $column->style }}">
                                    @if ($column->sortable)

                                        @include('cms-models::model.partials.list.column_header_sort', [
                                            'sortKey'   => $key,
                                            'label'     => ucfirst($column->label),
                                            'active'    => $key === $sortColumn,
                                            'direction' => $sortDirection,
                                        ])
                                    @else
                                        {{ ucfirst($column->label) }}
                                    @endif

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

                            <tr class="records-row">
                                @if ($model->list->activatable)
                                    @include('cms-models::model.partials.list.column_activate', [
                                        'model'  => $model,
                                        'record' => $record,
                                    ])
                                @endif

                                @if ($model->list->orderable)
                                    @include('cms-models::model.partials.list.column_orderable', [
                                        'model'         => $model,
                                        'record'        => $record,
                                        'isDraggable'   => $draggableForOrderable,
                                        'sortDirection' => $sortDirection
                                    ])
                                @endif

                                @foreach ($model->list->columns as $column)
                                    @continue($column->hide)

                                    @include('cms-models::model.partials.list.column_strategy', [
                                        'record' => $record,
                                        'model'  => $model,
                                    ])

                                @endforeach

                                @if (cms_auth()->can(["{$permissionPrefix}edit", "{$permissionPrefix}delete"]))
                                    <td>
                                        <div class="btn-group btn-group-xs record-actions pull-right tr-show-on-hover" role="group">

                                            @if (cms_auth()->can("{$permissionPrefix}edit"))
                                                <a class="btn btn-default edit-record-action" href="{{ route($route, [ $record->getKey() ]) }}" role="button"
                                                   title="{{ ucfirst(cms_trans('common.action.edit')) }}"
                                                ><i class="fa fa-edit"></i></a>
                                            @endif

                                            @if (cms_auth()->can("{$permissionPrefix}delete"))
                                                <a class="btn btn-danger delete-record-action" href="#" role="button"
                                                   data-id="{{ $record->getKey() }}"
                                                   data-toggle="modal" data-target="#delete-record-modal"
                                                   title="{{ ucfirst(cms_trans('common.action.delete')) }}"
                                                ><i class="fa fa-trash-o"></i></a>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>

                        @endforeach

                    </tbody>
                </table>

                <div class="listing-footer clearfix">

                    @if (method_exists($records, 'links'))
                        @include('cms-models::model.partials.list.pagination', [
                            'records'         => $records,
                            'pageSize'        => $pageSize,
                            'pageSizeOptions' => $pageSizeOptions,
                        ])
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


    <div id="delete-record-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ ucfirst(cms_trans('common.action.close')) }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title delete-modal-title">
                        {{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->verbose_name }}
                    </h4>
                </div>
                <div class="modal-body">
                    <p class="text-danger">{{ cms_trans('common.cannot-undo') }}</p>
                </div>
                <div class="modal-footer">
                    <form class="delete-modal-form" method="post" data-url="{{ cms_route("{$routePrefix}.destroy", [ 'IDHERE' ]) }}" action="">
                        {{ method_field('delete') }}
                        {{ csrf_field() }}

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            {{ ucfirst(cms_trans('common.action.close')) }}
                        </button>
                        <button type="submit" class="btn btn-danger delete-modal-button">
                            {{ ucfirst(cms_trans('common.action.delete')) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($model->list->orderable)
        <div id="orderable-position-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <form class="orderable-position-modal-form" method="post"
                          data-url="{{ cms_route("{$routePrefix}.position", [ 'IDHERE' ]) }}" action="">
                        {{ method_field('put') }}
                        {{ csrf_field() }}

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="{{ ucfirst(cms_trans('common.action.close')) }}">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">
                                {{ ucfirst(cms_trans('models.orderable.move-to-position')) }}
                            </h4>
                        </div>

                        <div class="modal-body">

                            <div class="form-group">
                                <label for="orderable-position-input">{{ cms_trans('models.orderable.position') }}</label>
                                <input type="number" class="form-control text-right" id="orderable-position-input" value="1">
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">
                                {{ ucfirst(cms_trans('common.action.close')) }}
                            </button>
                            <button id="orderable-position-modal-button" class="btn btn-danger">
                                {{ ucfirst(cms_trans('common.action.save')) }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection


@push('javascript-end')
    <script>

        $('.delete-record-action').click(function () {
            var form = $('.delete-modal-form');
            form.attr(
                'action',
                form.attr('data-url').replace('IDHERE', $(this).attr('data-id'))
            );
            $('.delete-modal-title').text(
                    '{{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->verbose_name }} #' + $(this).attr('data-id')
            );
        });

        {{-- Activatable --}}
        @if ($model->list->activatable)
            $('.activate-toggle').click(function() {
                var id     = $(this).attr('data-id'),
                    state  = parseInt($(this).attr('data-active'), 10) ? true : false,
                    url    = '{{ cms_route("{$routePrefix}.activate", [ 'IDHERE' ]) }}',
                    parent = $(this);

                var data  = {
                    'activate' : ! state
                };

                url = url.replace('IDHERE', id);

                // switch to loading icon
                parent.find('.loading').removeClass('hidden');
                parent.find('.active, .inactive').addClass('hidden');

                $.ajax(url, {
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    'method'      : 'PUT',
                    'data'        : JSON.stringify(data),
                    'contentType' : 'application/json'
                })
                    .success(function (data) {

                        var active = data.active;

                        if ( ! data.success) {
                            console.log('Failed to updated active status...');
                            active = state;
                        }

                        parent.attr('data-active', active ? 1 : 0);

                        if (active) {
                            parent.find('.active').removeClass('hidden');
                            parent.closest('.activate-toggle').addClass('tr-show-on-hover');
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                            parent.closest('.activate-toggle').removeClass('tr-show-on-hover');
                        }
                        parent.find('.loading').addClass('hidden');

                    })
                    .error(function (xhr, status, error) {
                        console.log('activate error: ' + error);

                        if (state) {
                            parent.find('.active').removeClass('hidden');
                            parent.closest('.activate-toggle').addClass('tr-show-on-hover');
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                            parent.closest('.activate-toggle').removeClass('tr-show-on-hover');
                        }
                        parent.find('.loading').addClass('hidden');
                    });
            });

            $(function () {
                $('.column-activate [data-toggle="tooltip"]').tooltip({
                    delay: {
                        'show': 250,
                        'hide': 50
                    }
                })
            });
        @endif

        {{-- Orderable: --}}
        @if ($model->list->orderable)

            var openOrderableModal = function (parent) {
                var form = $('form.orderable-position-modal-form');
                form.attr(
                    'action',
                    form.attr('data-url').replace('IDHERE', parent.attr('data-id'))
                );

                var input = $('#orderable-position-input');

                // set the current value on the input field
                input.val( parent.attr('data-position') );

                setTimeout(function() { input.focus().select(); }, 500);

                // store the index of the table row on the modal
                $('#orderable-position-modal').data('data-tr-index', parent.closest('tr').index());
            };

            var setOrderablePosition = function(parent, position) {

                var url  = '{{ cms_route("{$routePrefix}.position", [ 'IDHERE' ]) }}',
                    id   = parent.attr('data-id'),
                    data = { 'position' : position };

                url = url.replace('IDHERE', id);

                // loading state
                parent.find('.orderable-drag-drop .move').addClass('hidden');
                parent.find('.orderable-drag-drop .loading').removeClass('hidden');

                $.ajax(url, {
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    'method'      : 'PUT',
                    'data'        : JSON.stringify(data),
                    'contentType' : 'application/json'
                })
                    .success(function (data) {
                        var position = data.position;

                        if ( ! data.success) {
                            console.log('Failed to update orderable position...');
                            position = null;
                        }

                        location.reload();
                    })
                    .error(function (xhr, status, error) {
                        console.log('orderable position error: ' + error);
                        parent.find('.orderable-drag-drop .loading').addClass('hidden');
                        parent.find('.orderable-drag-drop .move').removeClass('hidden');
                    });
            };

            $('.orderable-action-up').click(function (event) {
                event.preventDefault();
                setOrderablePosition($(this).closest('.column-orderable'), 'up');
            });

            $('.orderable-action-down').click(function (event) {
                event.preventDefault();
                setOrderablePosition($(this).closest('.column-orderable'), 'down');
            });

            $('.orderable-action-top').click(function (event) {
                event.preventDefault();
                setOrderablePosition($(this).closest('.column-orderable'), 'top');
            });

            $('.orderable-action-bottom').click(function (event) {
                event.preventDefault();
                setOrderablePosition($(this).closest('.column-orderable'), 'bottom');
            });

            $('.orderable-action-remove').click(function (event) {
                event.preventDefault();
                setOrderablePosition($(this).closest('.column-orderable'), 'remove');
            });

            $('.orderable-action-position').click(function (event) {
                event.preventDefault();
                openOrderableModal($(this).closest('.column-orderable'));
            });

            $('#orderable-position-modal-button').click(function (event) {
                event.preventDefault();

                // get column-orderable parent for row index
                var index  = $('#orderable-position-modal').data('data-tr-index');
                var parent = $('tr.records-row').eq(index).find('.column-orderable');

                setOrderablePosition(parent, $('#orderable-position-input').val());
            });

        @endif

        {{-- Orderable: drag and drop --}}
        @if ($model->list->orderable && $model->list->getOrderableColumn() === $sortColumn)
            $(function () {
                $('.records-table').sortable({
                    handle            : '.orderable-drag-drop',
                    containerSelector : 'table',
                    itemPath          : '> tbody',
                    itemSelector      : 'tr',
                    placeholder       : '<tr class="orderable-placeholder"/>',
                    bodyClass         : 'orderable-dragging',
                    draggedClass      : 'orderable-dragged',
                    onDrop            : function($item, container, _super) {
                        _super($item, container);

                        var oldPosition = parseInt( $($item).find('.column-orderable').attr('data-position'), 10),
                            newPosition,
                            newInList,
                            relative;

                        // determine the new position to set by the surrounding items: base position is the top of the
                        // two positions between which the item should end up
                        if ($item.index() > 0) {
                            relative = $($item).prev().find('.column-orderable');
                            newPosition = parseInt( relative.attr('data-position'), 10);
                        } else {
                            relative = $($item).next().find('.column-orderable');
                            newPosition = parseInt( relative.attr('data-position'), 10) - 1;
                        }
                        newInList = parseInt( relative.attr('data-in-list'), 10) > 0;


                        if ( ! newInList) {
                            // if we're dragging the item out of the list, move it to the bottom for now
                            newPosition = 'bottom';
                        } else {
                            // return if the position is unchanged
                            if (oldPosition == newPosition || oldPosition == newPosition + 1) {
                                return;
                            }

                            // adjust if we're dragging an item up
                            if (oldPosition > newPosition) {
                                newPosition += 1;
                            }
                        }

                        // initiate ajax call
                        setOrderablePosition($($item).find('.column-orderable'), newPosition);
                    }
                });
            });

            $(function () {
                $('.column-orderable [data-toggle="tooltip"]').tooltip({
                    delay: {
                        'show': 500,
                        'hide': 50
                    }
                })
            });
        @endif

    </script>
@endpush

