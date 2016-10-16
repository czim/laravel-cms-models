
<input id="field-{{ $key }}"
       type="{{ $type ?: 'text' }}"
       name="{{ $name ?: $key }}"
       value="{{ $value }}"
       class="form-control"
       @if ($required && ! $translated) required="required" @endif
>

