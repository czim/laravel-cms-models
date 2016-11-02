<?php
namespace Czim\CmsModels\Http\Controllers\FormFieldStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelReferenceRepositoryInterface;
use Czim\CmsModels\Contracts\Support\MetaReferenceDataProviderInterface;
use Illuminate\Database\Eloquent\Model;

class RelationSingleKeyedReference extends RelationSingleKey
{

    /**
     * Returns the value for a single related model.
     *
     * @param Model|null $model
     * @return mixed|null
     */
    protected function getValueFromModel($model)
    {
        if ( ! ($model instanceof Model)) {
            return null;
        }

        $reference = null;

        $referenceData = $this->getMetaReferenceProvider()->getForModelClassByType(
            get_class($this->model),
            'form.field',
            $this->formFieldData->key()
        );

        if ($referenceData) {
            $reference = $this->getModelReferenceRepository()->getReferenceForModelMetaReferenceByModel($referenceData, $model);
        }
        
        return [
            'key'       => $model->getKey(),
            'reference' => $reference,
        ];
    }


    /**
     * @return ModelReferenceRepositoryInterface
     */
    protected function getModelReferenceRepository()
    {
        return app(ModelReferenceRepositoryInterface::class);
    }

    /**
     * @return MetaReferenceDataProviderInterface
     */
    protected function getMetaReferenceProvider()
    {
        return app(MetaReferenceDataProviderInterface::class);
    }

}
