@extends(cms_config('views.layout'))

<?php
    if ($creating) {
        $title = $model->verbose_name . ' - ' . cms_trans('common.action.create');
    } else {
        $title = $model->verbose_name . ' (' . $record->getKey() .  ') - ' . cms_trans('common.action.edit');
    }
?>

@section('title', $title)

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li>
            <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
                {{ cms_trans('common.home') }}
            </a>
        </li>
        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ $model->verbose_name_plural }}
            </a>
        </li>
        <li class="active">
            {{ $title }}
        </li>
    </ol>
@endsection


@section('content')

    <div class="page-header">
        <h1>{{ $title }}</h1>
    </div>

    @if ($creating)
        <form method="post" action="{{ cms_route("{$routePrefix}.store") }}">
    @else
        <form method="post" action="{{ cms_route("{$routePrefix}.update", [ $record->getKey() ]) }}">
            {{ method_field('put') }}
    @endif
            {{ csrf_field() }}

        @if ($model->form->hasTabs())

            <?php
                $tabs = $model->form->tabs();
            ?>

            <div class="tab-container">

                @include('cms-models::model.partials.form.tab_lips', compact(
                    'record',
                    'model',
                    'tabs',
                    'routePrefix',
                    'permissionPrefix'
                ))

                @include('cms-models::model.partials.form.tab_panes', compact(
                    'record',
                    'model',
                    'tabs',
                    'routePrefix',
                    'permissionPrefix'
                ))

            </div>

        @else

            @foreach ($model->form->layout() as $nodeKey => $node)

                    @include('cms-models::model.partials.form.layout_node', array_merge(
                        compact(
                            'node',
                            'nodeKey',
                            'record',
                            'model'
                        ),
                        [
                            'parent' => null,
                        ]
                    ))

            @endforeach
        @endif

        @include('cms-models::model.partials.form.buttons', compact('record', 'model'))

    </form>

@endsection
