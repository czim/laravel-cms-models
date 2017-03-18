<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Czim\Listify\Listify;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class AbstractTestModel extends Model
{
    use Listify,
        Translatable;

    protected $fillable = [
        'name',
    ];

    protected $translatedAttributes = [];

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
    
}
