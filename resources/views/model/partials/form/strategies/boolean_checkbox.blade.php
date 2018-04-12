<input type="hidden"
       name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}"
       value="0"
>
<input id="field-{{ $key }}"
       type="checkbox"
       name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}"
       @if ($value) checked="checked" @endif
>

@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])
