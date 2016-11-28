
<select id="field-{{ $key }}"
        name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}"
        class="form-control"
>
    <option value=""
            @if (null === $value)
            selected="selected"
            @endif
    ></option>

    <?php
        $selectOptions = [
            1 => cms_trans('common.boolean.true'),
            0 => cms_trans('common.boolean.false'),
        ];
    ?>

    @foreach ($selectOptions as $selectKey => $display)

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
