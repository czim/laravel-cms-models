
<input id="field-{{ $key }}"
       type="checkbox"
       name="{{ $name ?: $key }}"
       @if ($value) checked="checked" @endif
>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))
