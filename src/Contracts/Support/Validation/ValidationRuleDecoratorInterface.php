<?php
namespace Czim\CmsModels\Contracts\Support\Validation;

use Illuminate\Database\Eloquent\Model;

interface ValidationRuleDecoratorInterface
{

    /**
     * Decorates given validation rules
     *
     * @param array      $rules
     * @param Model|null $model     if updating, the model being updated
     * @return array
     */
    public function decorate(array $rules, Model $model = null);

}
