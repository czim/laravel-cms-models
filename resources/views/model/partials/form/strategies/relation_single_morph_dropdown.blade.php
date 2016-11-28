
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       @if ($required && ! $translated) required="required" @endif
>
    @if ( ! $required)
        <option></option>
    @endif

    @foreach ($dropdownOptions as $groupKey => $groupOptions)

        <optgroup label="{{ array_get($modelLabels, $groupKey, $groupKey) }}">
            @foreach ($groupOptions as $optionValue => $optionDisplay)

                <option value="{{ $optionValue }}" @if ($optionValue == $value) selected="selected" @endif>
                    {{ $optionDisplay }}
                </option>

            @endforeach
        </optgroup>

    @endforeach

</select>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])
