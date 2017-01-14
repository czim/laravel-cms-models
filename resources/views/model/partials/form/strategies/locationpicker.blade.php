
<div class="locationpicker-inputs">

    <div class="form-group clearfix">
        <label class="control-label col-sm-3" for="field-{{ $key }}__location">
            {{ cms_trans('models.location.type-location') }}
        </label>
        <div class="input-group col-sm-9">
            <span class="input-group-addon">
                <span class="glyphicon glyphicon-map-marker"></span>
            </span>
            <input id="field-{{ $key }}__location"
                   type="{{ $type ?: 'text' }}"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[location]"
                   value="{{ array_get($value, 'location', $defaultLocation) }}"
                   class="form-control"
                   size="{{ array_get($options, 'length', array_get($options, 'size')) }}"
                   maxlength="{{ array_get($options, 'length') }}"
            >
        </div>
    </div>

    <div class="form-group clearfix">
        <label class="control-label col-sm-3" for="field-{{ $key }}__latitude">
            {{ cms_trans('models.location.lat-long-label') }}
        </label>
        <div class="input-group col-sm-9" id="__locationpicker__{{ $key }}__latlong">

            <input id="field-{{ $key }}__latitude"
                   type="{{ $type ?: 'text' }}"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[latitude]"
                   value="{{ array_get($value, 'latitude', $defaultLatitude) }}"
                   class="form-control"
                   size="10"
                   placeholder="{{ cms_trans('models.location.latitude') }}"
                   @if ($required && ! $translated) required="required" @endif
            >

            <span class="input-group-btn" style="width: 0;"></span>

            <input id="field-{{ $key }}__longitude"
                   type="{{ $type ?: 'text' }}"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[longitude]"
                   value="{{ array_get($value, 'longitude', $defaultLongitude) }}"
                   class="form-control"
                   size="10"
                   placeholder="{{ cms_trans('models.location.longitude') }}"
                   style="margin-left: -1px"
                   @if ($required && ! $translated) required="required" @endif
            >
        </div>
    </div>
</div>

<div id="field-{{ $key }}__map" class="locationpicker-map" title="{{ cms_trans('models.location.drag-location') }}"></div>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@push('javascript-end')
    <!-- form field display strategy: locationpicker -->
    <?php
        $jsOptions = [
            'location' => [
                'latitude'  => array_get($value, 'latitude') ?: $defaultLatitude,
                'longitude' => array_get($value, 'longitude') ?: $defaultLongitude,
            ],
            'locationName' => array_get($value, 'location') ?: $defaultLocation,
            'radius' => 0,
            'enableAutocomplete' => true,
//            'enableAutocompleteBlur' => true,
            'enableReverseGeocode' => true,
            'markerInCenter' => true,
            'inputBinding' => [
                'latitudeInput'     => '[[LATITUDE_PLACEHOLDER]]',
                'longitudeInput'    => '[[LONGITUDE_PLACEHOLDER]]',
                'locationNameInput' => '[[LOCATION_PLACEHOLDER]]',
                //'radiusInput' => null,
            ]
        ];

        $encodedOptions = json_encode($jsOptions);

        // Replace placeholders to make proper jquery references
        $encodedOptions = str_replace(
            [
                '"[[LATITUDE_PLACEHOLDER]]"',
                '"[[LONGITUDE_PLACEHOLDER]]"',
                '"[[LOCATION_PLACEHOLDER]]"',
            ],
            [
                "$('#field-{$key}__latitude')",
                "$('#field-{$key}__longitude')",
                "$('#field-{$key}__location')",
            ],
            $encodedOptions
        );
    ?>
    <script>
        $(function () {
            $('#field-{{ $key }}__map').locationpicker({!! $encodedOptions !!});
        });
    </script>
@endpush

@push('javascript-head')
    <script src="//maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&sensor=false&libraries=places"></script>
@endpush
