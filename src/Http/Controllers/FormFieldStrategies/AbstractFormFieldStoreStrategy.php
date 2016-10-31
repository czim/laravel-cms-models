<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Analyzer\AttributeValidationResolver;
use Czim\CmsModels\Analyzer\RelationValidationResolver;
use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Http\Controllers\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\View\Traits\ResolvesSourceStrategies;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class AbstractFormFieldStoreStrategy implements FormFieldStoreStrategyInterface
{
    use ResolvesSourceStrategies;


    /**
     * @var array
     */
    protected $parameters = [];


    /**
     * Sets parameters to use for retrieving & storing.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Retrieves current values from a model
     *
     * @param Model  $model
     * @param string $source
     * @return mixed
     */
    public function retrieve(Model $model, $source)
    {
        if ($this->isTranslated()) {
            return $model->translations->pluck($source, config('translatable.locale_key', 'locale'))->toArray();
        }

        return $this->resolveModelSource($model, $source);
    }

    /**
     * Stores a submitted value on a model
     *
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    public function store(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStore($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStore($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * @param Model  $model
     * @param string $source
     * @param mixed  $value
     */
    protected function performStore(Model $model, $source, $value)
    {
        $value = $this->adjustValue($value);

        if (method_exists($model, $source)) {
            $model->{$source}($value);
            return;
        }

        $model->{$source} = $value;
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function storeAfter(Model $model, $source, $value)
    {
        if ( ! $this->isTranslated()) {
            $this->performStoreAfter($model, $source, $value);
            return;
        }

        /** @var Model|Translatable $model */

        if ( ! is_array($value)) {
            throw new UnexpectedValueException("Value should be in per-locale array format for translatable data");
        }

        foreach ($value as $locale => $singleValue) {
            $this->performStoreAfter($model->translateOrNew($locale), $source, $singleValue);
        }
    }

    /**
     * Stores a submitted value on a model, after it has been created (or saved).
     *
     * @param Model $model
     * @param mixed $source
     * @param mixed $value
     */
    public function performStoreAfter(Model $model, $source, $value)
    {
    }

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData|null $field
     * @param ModelInformationInterface|ModelInformation|null     $modelInformation
     * @return array|false      false if no validation should be performed.
     */
    public function validationRules(
        ModelFormFieldDataInterface $field = null,
        ModelInformationInterface $modelInformation = null
    ) {
        if ( ! $field || ! $modelInformation) {
            return false;
        }

        $rules = false;

        $key = $field->key();

        if (array_key_exists($key, $modelInformation->attributes)) {

            $rules = $this->getAttributeValidationResolver()->determineValidationRules(
                $modelInformation->attributes[ $key ],
                $field
            );

        } elseif (array_key_exists($key, $modelInformation->relations)) {

            $rules = $this->getRelationValidationResolver()->determineValidationRules(
                $modelInformation->relations[ $key ],
                $field
            );
        }

        // Translations are handled with locale-keyed associative arrays
        if ($rules && $field->translated()) {
            $rules = [
                $key        => 'array',
                $key . '.*' => $rules
            ];
        }

        return $rules;
    }


    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        return $value;
    }

    /**
     * @return bool
     */
    protected function isTranslated()
    {
        return in_array('translated', $this->parameters);
    }

    /**
     * @return bool
     */
    protected function isNullable()
    {
        return in_array('nullable', $this->parameters);
    }

    /**
     * @param Model $model
     * @return ModelInformation|false
     */
    protected function getModelInformation(Model $model)
    {
        return $this->getModelInformationRepository()->getByModel($model);
    }

    /**
     * @return ModelInformationRepositoryInterface
     */
    protected function getModelInformationRepository()
    {
        return app(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return AttributeValidationResolver
     */
    protected function getAttributeValidationResolver()
    {
        return app(AttributeValidationResolver::class);
    }

    /**
     * @return RelationValidationResolver
     */
    protected function getRelationValidationResolver()
    {
        return app(RelationValidationResolver::class);
    }
}
