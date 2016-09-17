@extends(cms_config('views.layout'))

@section('title', $model->verbose_name_plural)


@section('breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">Home</a></li>
        <li class="active">{{ $model->verbose_name_plural }}</li>
    </ol>
@endsection


@section('content')

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

                <table class="table">

                    <thead>
                        <tr>
                            @if ($model->list->activatable)
                                <th class="column column-activate"></th>
                            @endif

                            @foreach ($model->list->columns as $key => $column)
                                @continue($column->hide)

                                <th class="column {{ $column->style }}">
                                    @if ($column->sortable)

                                        @include('cms-models::model.partials.list.column_header_sort', [
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

                            <tr>
                                @if ($model->list->activatable)
                                    @include('cms-models::model.partials.list.column_activate', [
                                        'model'  => $model,
                                        'record' => $record,
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
                                        <div class="btn-group btn-group-xs pull-right" role="group" style="display: flex">

                                            @if (cms_auth()->can("{$permissionPrefix}edit"))
                                                <a class="btn btn-default edit-record-action" href="{{ route($route, [ $record->getKey() ]) }}" role="button"
                                                ><i class="fa fa-edit"></i></a>
                                            @endif

                                            @if (cms_auth()->can("{$permissionPrefix}delete"))
                                                <a class="btn btn-danger delete-record-action" href="#" role="button"
                                                   data-id="{{ $record->getKey() }}"
                                                   data-toggle="modal" data-target="#delete-record-modal"
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

                    <?php
                        $currentCount = method_exists($records, 'total') ? $records->total() : 0;
                    ?>

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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title delete-modal-title">Delete {{ $model->verbose_name }}</h4>
                </div>
                <div class="modal-body">
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <form class="delete-modal-form" method="post" data-url="{{ cms_route("{$routePrefix}.destroy", [ 'IDHERE' ]) }}" action="">
                        {{ method_field('delete') }}
                        {{ csrf_field() }}

                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger delete-modal-button">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('javascript-end')
    <script>
        $('.delete-record-action').click(function () {
            var form = $('.delete-modal-form');
            form.attr(
                'action',
                form.attr('data-url').replace('IDHERE', $(this).attr('data-id'))
            );
            $('.delete-modal-title').text('Delete {{ $model->verbose_name }} #' + $(this).attr('data-id'));
        });

        @if ($model->list->activatable)
            $('.activate-toggle').click(function() {
                var id     = $(this).attr('data-id'),
                    state  = parseInt($(this).attr('data-active'), 10) ? true : false,
                    url    = '{{ cms_route("{$routePrefix}.activate", [ 'IDHERE' ]) }}',
                    parent = $(this);

                var data  = {
                    'activate' : ! state
                };

                url = url.replace('IDHERE', $(this).attr('data-id'));

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
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                        }
                        parent.find('.loading').addClass('hidden');

                    })
                    .error(function (xhr, status, error) {
                        console.log('activate error: ' + error);

                        if (state) {
                            parent.find('.active').removeClass('hidden');
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                        }
                        parent.find('.loading').addClass('hidden');
                    });
            });
        @endif
    </script>
@endpush
