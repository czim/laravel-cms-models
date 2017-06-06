
<div class="form-group">
    <div class="input-group colorpicker-component" id="__colorpicker__{{ $key }}">

        <input id="field-{{ $key }}"
               type="text"
               name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}"
               value="{{ $value }}"
               class="form-control"
               size="{{ array_get($options, 'length', array_get($options, 'size')) }}"
               maxlength="{{ array_get($options, 'length') }}"
               @if ($required && ! $translated) required="required" @endif
        >
        <span class="input-group-addon"><i></i></span>
    </div>
</div>



@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@cms_script
    <!-- form field display strategy: colorpicker -->
    @php
        $jsOptions = [];

        if (array_get($options, 'format')) {
            $jsOptions['format'] = array_get($options, 'format');
        }
    @endphp

    <script>
        $(function() {
            $('#__colorpicker__{{ $key }}').colorpicker({!! json_encode($jsOptions) !!});
        });
    </script>
@cms_endscript
