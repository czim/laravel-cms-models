<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;

/**
 * Class RelationSingleDropdownStrategy
 *
 * Normal select dropdown for relation references.
 * Not advisable to to use with large resultsets.
 */
class RelationSingleDropdownStrategy extends AbstractRelationStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_single_dropdown';
    }

    /**
     * Enriches field data before passing it on to the view.
     *
     * @param array $data
     * @return array
     */
    protected function decorateFieldData(array $data)
    {
        // Get the key-reference pairs required to fill the drop-down

        $referenceData = $this->getReferenceDataProvider()->getForModelClassByType(
            $this->model,
            'form.field',
            $this->field->key()
        );

        if ($referenceData) {
            $references = $this->getReferenceRepository()->getReferencesForModelMetaReference($referenceData);
        } else {
            $references = [];
        }

        $data['dropdownOptions'] = $references;

        return $data;
    }

    /**
     * @return MetaReferenceDataProviderInterface
     */
    protected function getReferenceDataProvider()
    {
        return app(MetaReferenceDataProviderInterface::class);
    }

    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

}
