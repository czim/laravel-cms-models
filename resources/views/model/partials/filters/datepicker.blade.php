
<div class="form-group">
    <div class="input-group date" id="__filter_datepicker__{{ $key }}">

        <input id="filter.{{ $key }}"
               type="text"
               name="filter[{{ $key }}]"
               value="{{ $value }}"
               class="form-control input-sm"
               placeholder="{{ ucfirst($label) }}">

        <span class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </span>
    </div>
</div>


@push('javascript-end')
    <!-- filter strategy: datepicker -->
    <?php
        $jsOptions = [
            'format' => array_get($options, 'moment_format', 'YYYY-MM-DD'),
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
            $('#__filter_datepicker__{{ $key }}').datetimepicker({!! json_encode($jsOptions) !!});
        });
    </script>
@endpush
