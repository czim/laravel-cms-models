<?php
namespace Czim\CmsModels\Test\Helpers\Support;

use Czim\CmsModels\Support\Strategies\Traits\ResolvesSourceStrategies;
use Illuminate\Database\Eloquent\Model;

class UsesResolvesSourceStrategies
{
    use ResolvesSourceStrategies;

    /**
     * Passthru for testing the trait method.
     *
     * @param Model $model
     * @param mixed $source
     * @return mixed
     */
    public function publicResolveModelSource(Model $model, $source)
    {
        return $this->resolveModelSource($model, $source);
    }

}
