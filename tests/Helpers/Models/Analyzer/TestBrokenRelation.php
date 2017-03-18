<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestBrokenRelation extends Model
{

    public function testDoesNotReturnRelation()
    {
        return $this->hasMany('NotEvenAModel');
    }

    /**
     * @cms relation
     */
    public function testThrowsException()
    {
        throw new \RuntimeException();
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        return 'not a relation';
    }

}
