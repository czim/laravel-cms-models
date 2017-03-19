<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\AnalyzeRelations;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestActivatable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestBrokenRelation;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestExtendingModel;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestGlobalScope;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestMorphRelation;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestMorphToManyRelation;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOrderable;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestRelation;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestSpecialRelation;

/**
 * Class AnalyzeRelationsTest
 *
 * @group analysis
 */
class AnalyzeRelationsTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_analyzes_model_relations()
    {
        // Setup
        $model    = new TestRelation;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        $info->attributes = [
            'test_activatable_id' => new ModelAttributeData([
                'nullable' => true
            ]),
        ];

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['relations']);
        static::assertEquals(
            ['testBelongsTo', 'testHasOne', 'testHasMany', 'testBelongsToMany'],
            array_keys($info['relations'])
        );

        /** @var ModelRelationData $relation */
        $relation = $info['relations']['testBelongsTo'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::BELONGS_TO, $relation->type);
        static::assertEquals('testBelongsTo', $relation->method);
        static::assertEquals('testBelongsTo', $relation->name);
        static::assertEquals(TestActivatable::class, $relation->relatedModel);
        static::assertEquals(['test_activatable_id'], $relation->foreign_keys);
        static::assertEquals(true, $relation->nullable_key);

        $relation = $info['relations']['testHasOne'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::HAS_ONE, $relation->type);
        static::assertEquals('testHasOne', $relation->method);
        static::assertEquals('testHasOne', $relation->name);
        static::assertEquals(TestActivatable::class, $relation->relatedModel);
        static::assertEquals(['test_activatables.test_relation_id'], $relation->foreign_keys);

        $relation = $info['relations']['testHasMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::HAS_MANY, $relation->type);
        static::assertEquals('testHasMany', $relation->method);
        static::assertEquals('testHasMany', $relation->name);
        static::assertEquals(TestOrderable::class, $relation->relatedModel);
        static::assertEquals(['test_orderables.test_relation_id'], $relation->foreign_keys);

        $relation = $info['relations']['testBelongsToMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::BELONGS_TO_MANY, $relation->type);
        static::assertEquals('testBelongsToMany', $relation->method);
        static::assertEquals('testBelongsToMany', $relation->name);
        static::assertEquals(TestGlobalScope::class, $relation->relatedModel);
        static::assertEquals(['test_belongs_to_many.test_relation_id', 'test_belongs_to_many.test_global_scope_id'], $relation->foreign_keys);
    }

    /**
     * @test
     */
    function it_analyzes_morph_model_relations()
    {
        // Setup
        $model    = new TestMorphRelation;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        $info->attributes = [
            'morphable_id' => new ModelAttributeData([
                'nullable' => true
            ]),
            'morphable_type' => new ModelAttributeData([
                'nullable' => true
            ]),
        ];

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['relations']);
        static::assertEquals(
            ['testMorphTo', 'testMorphOne', 'testMorphMany', 'testMorphToMany', 'testMorphedByMany'],
            array_keys($info['relations'])
        );

        /** @var ModelRelationData $relation */
        $relation = $info['relations']['testMorphTo'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::MORPH_TO, $relation->type);
        static::assertEquals('testMorphTo', $relation->method);
        static::assertEquals('testMorphTo', $relation->name);
        static::assertEquals(TestMorphRelation::class, $relation->relatedModel);
        static::assertEquals(['morphable_id', 'morphable_type'], $relation->foreign_keys);
        static::assertEquals(true, $relation->nullable_key);

        $relation = $info['relations']['testMorphOne'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::MORPH_ONE, $relation->type);
        static::assertEquals('testMorphOne', $relation->method);
        static::assertEquals('testMorphOne', $relation->name);
        static::assertEquals(TestMorphRelation::class, $relation->relatedModel);

        $relation = $info['relations']['testMorphMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::MORPH_MANY, $relation->type);
        static::assertEquals('testMorphMany', $relation->method);
        static::assertEquals('testMorphMany', $relation->name);
        static::assertEquals(TestMorphRelation::class, $relation->relatedModel);

        $relation = $info['relations']['testMorphToMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::MORPH_TO_MANY, $relation->type);
        static::assertEquals('testMorphToMany', $relation->method);
        static::assertEquals('testMorphToMany', $relation->name);
        static::assertEquals(TestMorphToManyRelation::class, $relation->relatedModel);

        $relation = $info['relations']['testMorphedByMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::MORPHED_BY_MANY, $relation->type);
        static::assertEquals('testMorphedByMany', $relation->method);
        static::assertEquals('testMorphedByMany', $relation->name);
        static::assertEquals(TestMorphToManyRelation::class, $relation->relatedModel);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    function it_throws_an_exception_if_a_foreign_key_attribute_is_not_defined()
    {
        // Setup
        $model    = new TestRelation;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $step->analyze($info);
    }

    /**
     * @test
     */
    function it_silently_ignores_broken_relation_methods()
    {
        // Setup
        $model    = new TestBrokenRelation;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info                 = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['relations']);
        static::assertEmpty($info['relations']);
    }

    /**
     * @test
     */
    function it_allows_tags_to_force_and_ignore_methods_and_to_define_morph_targets()
    {
        // Setup
        $model    = new TestSpecialRelation;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        $info->attributes = [
            'test_activatable_id' => new ModelAttributeData(),
            'morphable_id'        => new ModelAttributeData(),
            'morphable_type'      => new ModelAttributeData(),
        ];

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['relations']);
        static::assertEquals(['testAlternativeFormat', 'testMorphWithModels'], array_keys($info['relations']));

        /** @var ModelRelationData $relation */
        $relation = $info['relations']['testAlternativeFormat'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::BELONGS_TO, $relation->type);
        static::assertEquals('testAlternativeFormat', $relation->method);
        static::assertEquals('testAlternativeFormat', $relation->name);
        static::assertEquals(TestActivatable::class, $relation->relatedModel);
        static::assertEquals(['test_activatable_id'], $relation->foreign_keys);
    }

    /**
     * @test
     */
    function it_analyzes_inherited_model_relations()
    {
        // Setup
        $model    = new TestExtendingModel;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        $info->attributes = [
            'test_activatable_id' => new ModelAttributeData([
                'nullable' => true
            ]),
        ];

        // Test
        $step = new AnalyzeRelations;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['relations']);
        static::assertEquals(
            ['testBelongsTo', 'testHasOne', 'testHasMany', 'testBelongsToMany'],
            array_keys($info['relations'])
        );

        /** @var ModelRelationData $relation */
        $relation = $info['relations']['testBelongsTo'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::BELONGS_TO, $relation->type);
        static::assertEquals('testBelongsTo', $relation->method);
        static::assertEquals('testBelongsTo', $relation->name);
        static::assertEquals(TestActivatable::class, $relation->relatedModel);
        static::assertEquals(['test_activatable_id'], $relation->foreign_keys);
        static::assertEquals(true, $relation->nullable_key);

        $relation = $info['relations']['testHasOne'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::HAS_ONE, $relation->type);
        static::assertEquals('testHasOne', $relation->method);
        static::assertEquals('testHasOne', $relation->name);
        static::assertEquals(TestActivatable::class, $relation->relatedModel);
        static::assertEquals(['test_activatables.test_relation_id'], $relation->foreign_keys);

        $relation = $info['relations']['testHasMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::HAS_MANY, $relation->type);
        static::assertEquals('testHasMany', $relation->method);
        static::assertEquals('testHasMany', $relation->name);
        static::assertEquals(TestOrderable::class, $relation->relatedModel);
        static::assertEquals(['test_orderables.test_relation_id'], $relation->foreign_keys);

        $relation = $info['relations']['testBelongsToMany'];
        static::assertInstanceOf(ModelRelationData::class, $relation);
        static::assertEquals(RelationType::BELONGS_TO_MANY, $relation->type);
        static::assertEquals('testBelongsToMany', $relation->method);
        static::assertEquals('testBelongsToMany', $relation->name);
        static::assertEquals(TestGlobalScope::class, $relation->relatedModel);
        static::assertEquals(['test_belongs_to_many.test_relation_id', 'test_belongs_to_many.test_global_scope_id'], $relation->foreign_keys);
    }

}
