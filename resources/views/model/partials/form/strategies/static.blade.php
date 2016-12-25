
@if ($displayStrategy)

    {!! $displayStrategy->render($record, $displaySource) !!}

@else

    <p id="field-{{ $key }}" class="form-control-static">
        {{ $value }}
    </p>

@endif

@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])
