<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class LocationStrategy extends AbstractDefaultStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.locationpicker';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        $data['hasTextField'] = (bool) array_get($this->field->options(), 'text');

        // Default location to use, if any
        if (    array_get($this->field->options(), 'default_latitude')
            &&  array_get($this->field->options(), 'default_longitude')
        ) {
            $data['defaultLocation']  = array_get($this->field->options(), 'default_location');
            $data['defaultLatitude']  = array_get($this->field->options(), 'default_latitude');
            $data['defaultLongitude'] = array_get($this->field->options(), 'default_longitude');
        } elseif (array_get($this->field->options(), 'default')) {
            $data['defaultLocation']  = config('cms-models.custom-strategies.location.default.location');
            $data['defaultLatitude']  = config('cms-models.custom-strategies.location.default.latitude');
            $data['defaultLongitude'] = config('cms-models.custom-strategies.location.default.longitude');
        } else {
            $data['defaultLocation']  = null;
            $data['defaultLatitude']  = null;
            $data['defaultLongitude'] = null;
        }

        $data['googleMapsApiKey'] = config('cms-models.api-keys.google-maps');

        return $data;
    }

}
