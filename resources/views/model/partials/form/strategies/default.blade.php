
<input id="field-{{ $key }}"
       type="{{ $type ?: 'text' }}"
       name="{{ $name ?: $key }}"
       value="{{ $value }}"
       class="form-control"
       @if ($required) required="required" @endif
>

