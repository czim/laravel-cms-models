
<select id="field-{{ $key }}"
        name="{{ $name ?: $key }}"
        class="form-control"
>
    @if ( ! $required)
    <option value=""
            @if (null === $value)
            selected="selected"
            @endif
    ></option>
    @endif

    @foreach ($dropdownOptions as $selectKey => $display)

        <option value="{{ $selectKey }}"
                @if (null !== $value && $value == $selectKey)
                selected="selected"
                @endif
        >
            {{ $display }}
        </option>
    @endforeach
</select>

@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])
