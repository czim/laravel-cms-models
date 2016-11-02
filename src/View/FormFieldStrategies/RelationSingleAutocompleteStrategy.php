<?php
namespace Czim\CmsModels\View\FormFieldStrategies;

class RelationSingleAutocompleteStrategy extends AbstractRelationStrategy
{

    /**
     * Returns the view partial that should be used.
     *
     * @return string
     */
    protected function getView()
    {
        return 'cms-models::model.partials.form.strategies.relation_single_autocomplete';
    }

}
