
<div class="form-group">
    <div class='input-group date' id="__datetimepicker__{{ $key }}">

        <input id="field-{{ $key }}"
               type="{{ $type ?: 'text' }}"
               name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}"
               value="{{ $value }}"
               class="form-control"
               size="{{ array_get($options, 'length', array_get($options, 'size')) }}"
               maxlength="{{ array_get($options, 'length') }}"
               @if ($required && ! $translated) required="required" @endif
        >
        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
</div>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@cms_script
    <!-- form field display strategy: datepicker datetime -->
    <?php
        $jsOptions = [
            'format' => array_get($options, 'moment_format', 'YYYY-MM-DD HH:mm'),
        ];

        if (array_get($options, 'view_mode')) {
            $jsOptions['viewMode'] = array_get($options, 'view_mode');
        }

        if (isset($minimumDate)) {
            $jsOptions['minDate'] = $minimumDate;
        }

        if (isset($maximumDate)) {
            $jsOptions['maxDate'] = $maximumDate;
        }

        if (isset($excludedDates) && count($excludedDates)) {
            $jsOptions['disabledDates'] = $excludedDates;
        }

    ?>
    <script>
        $(function () {
            $('#__datetimepicker__{{ $key }}').datetimepicker({!! json_encode($jsOptions) !!});
        });
    </script>
@cms_endscript
