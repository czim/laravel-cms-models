<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Illuminate\Database\Eloquent\Model;

class LocationFieldsStrategy extends DefaultStrategy
{

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        $data = [
            'longitude' => null,
            'latitude'  => null,
            'location'  => null,
        ];

        if ($latitude = $this->getAttributeLatitude()) {
            $data['latitude'] = (float) parent::retrieve($model, $latitude);
        }

        if ($longitude = $this->getAttributeLongitude()) {
            $data['longitude'] = (float) parent::retrieve($model, $longitude);
        }

        if ($text = $this->getAttributeText()) {
            $data['location'] = parent::retrieve($model, $text);
        }

        return $data;
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        if ($latitude = $this->getAttributeLatitude()) {
            $model->{$latitude} = $this->adjustValue(array_get($value, 'latitude'));
        }

        if ($longitude = $this->getAttributeLongitude()) {
            $model->{$longitude} = $this->adjustValue(array_get($value, 'longitude'));
        }

        if ($text = $this->getAttributeText()) {
            $model->{$text} = array_get($value, 'location');
        }
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null     null to fall back to default rules.
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        $key = $this->formFieldData->key();

        $rules = [
            $key . '.longitude' => [ 'numeric' ],
            $key . '.latitude'  => [ 'numeric' ],
            $key . '.text'      => [ 'string' ],
        ];

        // Always require long/lat if the field itself is required
        // If not, only require them if the attributes are non-nullable (todo)
        $required = $this->formFieldData->required();

        if ($required) {
            $rules[ $key . '.longitude' ][] = 'required';
            $rules[ $key . '.latitude' ][]  = 'required';
        }

        // todo: determine whether text column, if made available, is required

        return $rules;
    }

    /**
     * Returns attribute name for the 'latitude' attribute.
     *
     * @return string|false
     */
    protected function getAttributeLatitude()
    {
        $attribute = array_get($this->formFieldData->options(), 'latitude_name', 'latitude');

        if ( ! $attribute) {
            $attribute = false;
        }

        return $attribute;
    }

    /**
     * Returns attribute name for the 'longitude' attribute.
     *
     * @return string|false
     */
    protected function getAttributeLongitude()
    {
        $attribute = array_get($this->formFieldData->options(), 'longitude_name', 'longitude');

        if ( ! $attribute) {
            $attribute = false;
        }

        return $attribute;
    }

    /**
     * Returns attribute name for the 'location' text attribute.
     * This stores a textual representation of the location.
     *
     * @return string|false
     */
    protected function getAttributeText()
    {
        $attribute = array_get($this->formFieldData->options(), 'location_name', 'location');

        if ( ! $attribute) {
            $attribute = false;
        }

        return $attribute;
    }

}
