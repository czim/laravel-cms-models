<?php
namespace Czim\CmsModels\Strategies\Reference;

use Illuminate\Database\Eloquent\Model;

class IdAndAttribute extends AbstractReferenceStrategy
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
        $source = $this->resolveModelSource($model, $source);

        return '#' . $model->getKey() . ': ' . $source;
    }

}
