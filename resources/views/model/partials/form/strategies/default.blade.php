
<input id="field-{{ $key }}"
       type="{{ $type ?: 'text' }}"
       name="{{ $name ?: $key }}"
       value="{{ $value }}"
       class="form-control"
       @if ($required && ! $translated) required="required" @endif
>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))


