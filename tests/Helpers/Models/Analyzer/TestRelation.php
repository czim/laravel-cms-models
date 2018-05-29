<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestRelation extends Model
{
    protected $fillable = [
        'name',
    ];

    public function testBelongsTo()
    {
        return $this->belongsTo(TestActivatable::class, 'test_activatable_id');
    }

    public function testHasOne()
    {
        return $this->hasOne(TestActivatable::class, 'test_relation_id');
    }

    public function testHasMany()
    {
        return $this->hasMany(TestOrderable::class, 'test_relation_id');
    }

    public function testBelongsToMany()
    {
        return $this->belongsToMany(TestGlobalScope::class, 'test_belongs_to_many', 'test_relation_id', 'test_global_scope_id');
    }

    public function testMultiLine()
    {
        return $this
            ->hasMany(TestOrderable::class, 'test_relation_id');
    }

    /**
     * Public method that is not a relation.
     *
     * @return string
     */
    public function testIsNotARelationMethod()
    {
        return 'testing';
    }

}
