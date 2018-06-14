<?php
namespace Czim\CmsModels\Strategies\Form\Store;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\AttributeValidationResolver;
use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationValidationResolver;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelAttributeDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\Strategies\FormFieldStoreStrategyInterface;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Translation\TranslationLocaleHelper;
use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use UnexpectedValueException;

abstract class AbstractFormFieldStoreStrategy implements FormFieldStoreStrategyInterface
{
    use ResolvesSourceStrategies;


    /**
     * @var ModelFormFieldDataInterface|ModelFormFieldData
     */
    protected $formFieldData;

    /**
     * @var array
     */
    protected $parameters = [];


    /**
     * Sets the relevant form field data to provide a context.
     *
     * @param ModelFormFieldDataInterface $data
     * @return $this
     */
    public function setFormFieldData(ModelFormFieldDataInterface $data)
    {
        $this->formFieldData = $data;

        return $this;
    }

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
     * Returns field value based on list parent key data.
     *
     * Only relevant for store strategies that may be used for fields that correspond to list parent relations.
     * May simply return null otherwise.
     *
     * @param string $key
     * @return mixed
     */
    public function valueForListParentKey($key)
    {
        return $key;
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

            // Safeguard: use empty string if non-nullable translated field is null
            if (null === $singleValue && ! $this->isNullable()) {
                $singleValue = '';
            }

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
     * Performs finalizing/cleanup handling.
     *
     * @codeCoverageIgnore
     */
    public function finish()
    {
    }

    /**
     * Returns validation rules to use for submitted form data for this strategy.
     *
     * If the return array is associative, rules are expected nested per key,
     * otherwise the rules will be added to the top level key.
     *
     * @param ModelInformationInterface|ModelInformation|null $modelInformation
     * @param bool                                            $create
     * @return array|false false if no validation should be performed.
     */
    public function validationRules(ModelInformationInterface $modelInformation = null, $create)
    {
        if ( ! ($field = $this->formFieldData)) {
            return false;
        }

        return $this->getStrategySpecificRules($field);
    }

    /**
     * Returns validation rules specific for the strategy.
     *
     * Override this to set validation rules that should override attribute- or
     * relation-based validation rules.
     *
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @return array|false|null     null to fall back to default rules.
     * @codeCoverageIgnore
     */
    protected function getStrategySpecificRules(ModelFormFieldDataInterface $field = null)
    {
        return null;
    }

    /**
     * Adjusts or normalizes a value before storing it.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function adjustValue($value)
    {
        if ($this->isNullable() && empty($value)) {
            return null;
        }

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

}
