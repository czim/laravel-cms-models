
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       @if ($required && ! $translated) required="required" @endif
>
    @foreach ($dropdownOptions as $optionValue => $optionDisplay)

        <option value="{{ $optionValue }}" @if ($optionValue == $value) selected="selected" @endif>
            {{ $optionDisplay }}
        </option>

    @endforeach
</select>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))
