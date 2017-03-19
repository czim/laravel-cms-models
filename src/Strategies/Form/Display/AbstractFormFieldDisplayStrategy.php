<?php
namespace Czim\CmsModels\Strategies\Form\Display;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsModels\Contracts\Data\ModelFormFieldDataInterface;
use Czim\CmsModels\Contracts\View\FormFieldDisplayInterface;
use Czim\CmsModels\Support\Data\ModelFormFieldData;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

abstract class AbstractFormFieldDisplayStrategy implements FormFieldDisplayInterface
{

    /**
     * The view to use for rendering translated form fields.
     *
     * @var string
     */
    const FORM_FIELD_TRANSLATED_VIEW = 'cms-models::model.partials.form.strategies.default_translated';


    /**
     * @var Model
     */
    protected $model;

    /**
     * @var ModelFormFieldDataInterface|ModelFormFieldData
     */
    protected $field;


    /**
     * Renders a form field.
     *
     * @param Model                                          $model
     * @param ModelFormFieldDataInterface|ModelFormFieldData $field
     * @param mixed                                          $value
     * @param mixed                                          $originalValue
     * @param array                                          $errors
     * @return string
     */
    public function render(
        Model $model,
        ModelFormFieldDataInterface $field,
        $value,
        $originalValue,
        array $errors = []
    ) {
        $this->model = $model;
        $this->field = $field;

        if ($field->translated) {
            return $this->renderTranslatedFields($this->getRelevantLocales(), $value, $originalValue, $errors);
        }

        return $this->renderField($value, $originalValue, $errors);
    }

    /**
     * Returns a rendered form field.
     *
     * @param mixed       $value
     * @param mixed       $originalValue
     * @param array       $errors
     * @param null|string $locale if for single translated locale, the locale
     * @return string
     */
    abstract protected function renderField($value, $originalValue, array $errors = [], $locale = null);


    /**
     * Returns a rendered set of translated form fields.
     *
     * @param array $locales
     * @param mixed $value
     * @param mixed $originalValue
     * @param array $errors
     * @return string|View
     */
    protected function renderTranslatedFields(array $locales, $value, $originalValue, array $errors)
    {
        // Render the inputs for all relevant locales
        $rendered = [];

        foreach ($locales as $locale) {

            $rendered[ $locale ] = $this->renderField(
                $this->getValueForLocale($locale, $value),
                $this->getValueForLocale($locale, $originalValue),
                $this->getErrorsForLocale($locale, $errors),
                $locale
            );
        }

        return view(static::FORM_FIELD_TRANSLATED_VIEW, [
            'field'          => $this->field,
            'locales'        => $locales,
            'value'          => $value,
            'errors'         => $errors,
            'localeRendered' => $rendered,
        ]);
    }

    /**
     * Returns relevant value for single locale.
     *
     * @param string $locale
     * @param array  $value
     * @return mixed
     */
    protected function getValueForLocale($locale, $value)
    {
        if ( ! is_array($value)) {
            throw new UnexpectedValueException(
                "Translated form value should be an array for '{$this->field->key()}'"
            );
        }

        return array_get($value, $locale);
    }

    /**
     * Returns relevant errors array for single locale.
     *
     * @param string $locale
     * @param array  $errors
     * @return mixed
     */
    protected function getErrorsForLocale($locale, array $errors)
    {
        return array_get($errors, $locale, []);
    }

    /**
     * Returns available locales for which translated values may be set.
     *
     * @return string[]
     */
    protected function getRelevantLocales()
    {
        return $this->getLocaleRepository()->getAvailable();
    }

    /**
     * @return LocaleRepositoryInterface
     */
    protected function getLocaleRepository()
    {
        return app(LocaleRepositoryInterface::class);
    }

}
