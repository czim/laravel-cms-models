@extends(cms_config('views.layout'))

<?php
    $title = ucfirst($model->verbose_name) . ' (' . $record->getKey() .  ')';
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
                {{ ucfirst($model->verbose_name_plural) }}
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

    {{-- Before view --}}
    @if ($model->show->before && $model->show->before->view)
        @include($model->show->before->view, $model->show->before->variables())
    @endif


    @foreach ($model->show->fields as $key => $field)

        @include('cms-models::model.partials.show.field_strategy', [
            'key'      => $key,
            'field'    => $field,
            'strategy' => $fieldStrategies[ $key ],
            'record'   => $record,
            'model'    => $model,
        ])

    @endforeach


    {{-- After view --}}
    @if ($model->show->after && $model->show->after->view)
        @include($model->show->after->view, $model->show->after->variables())
    @endif

@endsection
