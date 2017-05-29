
<div class="form-group">


    <div class="col-sm-5" style="padding-left: 0">
        <div class="input-group date" id="__datetimepicker__{{ $key }}__from">

            <input id="field-{{ $key }}__from"
                   type="{{ $type ?: 'text' }}"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[from]"
                   value="{{ array_get($value, 'from') }}"
                   class="form-control"
                   size="{{ array_get($options, 'length', array_get($options, 'size')) }}"
                   maxlength="{{ array_get($options, 'length') }}"
                   @if (array_get($options, 'from_required') && ! $translated) required="required" @endif
            >
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
        </div>
    </div>

    <div class="col-sm-2 text-center">
        &mdash;
    </div>

    <div class="col-sm-5" style="padding-right: 0">
        <div class="input-group date" id="__datetimepicker__{{ $key }}__to">

            <input id="field-{{ $key }}__to"
                   type="{{ $type ?: 'text' }}"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[to]"
                   value="{{ array_get($value, 'to') }}"
                   class="form-control"
                   size="{{ array_get($options, 'length', array_get($options, 'size')) }}"
                   maxlength="{{ array_get($options, 'length') }}"
                   @if (array_get($options, 'to_required') && ! $translated) required="required" @endif
            >
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
        </div>
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
        $('#__datetimepicker__{{ $key }}__from').datetimepicker({!! json_encode($jsOptions) !!});
        $('#__datetimepicker__{{ $key }}__to').datetimepicker({!! json_encode($jsOptions) !!});
    });
</script>
@cms_endscript
