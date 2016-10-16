
<textarea id="field-{{ $key }}"
    name="{{ $name ?: $key }}"
    class="form-control"
    rows="{{ array_get($options, 'rows') }}"
    cols="{{ array_get($options, 'cols') }}"
    @if ($required && ! $translated) required="required" @endif
>{{ $value }}</textarea>

