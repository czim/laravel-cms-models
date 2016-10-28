<?php
namespace Czim\CmsModels\View\ReferenceStrategies;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DefaultReference
 *
 * Very basic direct source display default.
 */
class DefaultReference extends AbstractReferenceStrategy
{

    /**
     * Returns model reference string
     *
     * @param Model  $model
     * @param string $source
     * @return string
     */
    public function render(Model $model, $source)
    {
        return $this->resolveModelSource($model, $source);
    }

}
