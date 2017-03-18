<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestSpecialRelation extends Model
{

    /**
     * This method is not normally recognizable as a relation by the analyzer,
     * but is flagged as a relation with the cms docblock tag.
     *
     * @cms relation
     */
    public function testAlternativeFormat()
    {
        $model = TestActivatable::class;

        $relation = $this->belongsTo($model, 'test_activatable_id');

        return $relation;
    }

    /**
     * This method is normally detected as a relation, but should be ignored
     * since it is flagged ignore with the cms docblock tag.
     *
     * @cms ignore
     */
    public function testIgnoredRelation()
    {
        return $this->belongsTo(TestActivatable::class, 'test_activatable_id');
    }

    /**
     * This method is normally detected as a relation, but should be ignored
     * since it is flagged ignore with the cms docblock tag.
     *
     * @cms morph TestModel\Name,TestModelAnother\Name
     */
    public function testMorphWithModels()
    {
        return $this->morphTo('morphable');
    }

}
