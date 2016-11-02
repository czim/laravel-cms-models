
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       @if ($required && ! $translated) required="required" @endif
>
    @foreach ($dropdownOptions as $optionValue => $optionDisplay)

        <?php
            $selectedValue = is_array($value) ? array_get($value, 'key') : $value;
        ?>

        <option value="{{ $optionValue }}" @if ($optionValue == $selectedValue) selected="selected" @endif>
            {{ $optionDisplay }}
        </option>

    @endforeach
</select>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))
