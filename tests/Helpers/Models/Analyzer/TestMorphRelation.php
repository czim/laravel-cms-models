<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestMorphRelation extends Model
{
    protected $fillable = [
        'name',
    ];

    public function testMorphTo()
    {
        return $this->morphTo('morphable');
    }

    public function testMorphOne()
    {
        return $this->morphOne(TestMorphRelation::class, 'morphable');
    }

    public function testMorphMany()
    {
        return $this->morphMany(TestMorphRelation::class, 'morphable');
    }

    public function testMorphToMany()
    {
        return $this->morphToMany(TestMorphToManyRelation::class, 'morphable');
    }

    public function testMorphedByMany()
    {
        return $this->morphedByMany(TestMorphToManyRelation::class, 'morphable');
    }

}
