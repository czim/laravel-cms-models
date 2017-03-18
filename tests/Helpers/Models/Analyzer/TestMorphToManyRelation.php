<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestMorphToManyRelation extends Model
{

    public function testMorphedByMany()
    {
        return $this->morphedByMany(TestMorphRelation::class, 'morphable');
    }

}
