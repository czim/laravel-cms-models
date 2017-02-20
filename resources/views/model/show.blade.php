@extends(cms_config('views.layout'))

<?php
    $title = ucfirst($model->label())
           . ' ' . (trim($recordReference) ?: ($record->incrementing ? '#' . $record->getKey() : "'" . $record->getKey() .  "'"));
?>

@section('title', $title)


@section('breadcrumbs')
    @include('cms-models::model.partials.detail_breadcrumbs', compact(
        'model',
        'routePrefix',
        'title',
        'hasActiveListParent',
        'listParents'
    ))
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
