<?php
namespace Czim\CmsModels\Test\Helpers\Support;

use Illuminate\Database\Eloquent\Model;

class SourceMethodTestHelper
{

    /**
     * @param Model $model
     * @return mixed
     */
    public function source(Model $model)
    {
        $attribute = app()->bound('source-method-test-helper-method')
            ?   app('source-method-test-helper-method')
            :   'title';

        return $model->{$attribute};
    }

}
