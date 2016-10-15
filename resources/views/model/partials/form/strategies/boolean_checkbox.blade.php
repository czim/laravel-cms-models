
<input id="field-{{ $key }}"
       type="checkbox"
       name="{{ $name ?: $key }}"
       @if ($value) checked="checked" @endif
>

