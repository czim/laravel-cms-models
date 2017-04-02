<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationStrategyResolver;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Support\Enums\ExportColumnStrategy;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\FormStoreStrategy;
use Czim\CmsModels\Support\Enums\ListDisplayStrategy;
use Czim\CmsModels\Test\TestCase;

/**
 * Class RelationStrategyResolverTest
 *
 * @group analysis
 */
class RelationStrategyResolverTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      List
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_list_strategy_for_singular_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_REFERENCE, $resolver->determineListDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_ONE,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_REFERENCE, $resolver->determineListDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_TO,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_REFERENCE, $resolver->determineListDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_list_strategy_for_plural_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO_MANY,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_COUNT, $resolver->determineListDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_MANY,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_COUNT, $resolver->determineListDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_MANY,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_COUNT, $resolver->determineListDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPHED_BY_MANY,
        ]);
        static::assertEquals(ListDisplayStrategy::RELATION_COUNT, $resolver->determineListDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_list_strategy()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => 'unknown',
        ]);
        static::assertNull($resolver->determineListDisplayStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Display
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_singular_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_SINGLE_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_ONE,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_SINGLE_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_ONE,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_SINGLE_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_plural_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO_MANY,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_MANY,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPHED_BY_MANY,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_display_strategy_for_morph_to_relation()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_TO,
        ]);
        static::assertEquals(FormDisplayStrategy::RELATION_SINGLE_MORPH_AUTOCOMPLETE, $resolver->determineFormDisplayStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_form_display_strategy()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => 'unknown',
        ]);
        static::assertNull($resolver->determineFormDisplayStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Store
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_singular_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_SINGLE_KEY, $resolver->determineFormStoreStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_ONE,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_SINGLE_KEY, $resolver->determineFormStoreStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_ONE,
            'translated' => true,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_SINGLE_KEY . ':translated', $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_plural_relations()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO_MANY,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_PLURAL_KEYS, $resolver->determineFormStoreStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::HAS_MANY,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_PLURAL_KEYS, $resolver->determineFormStoreStrategy($data));

        $data = new ModelRelationData([
            'type' => RelationType::MORPHED_BY_MANY,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_PLURAL_KEYS, $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_determines_form_store_strategy_for_morph_to_relation()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => RelationType::MORPH_TO,
            'translated' => false,
        ]);
        static::assertEquals(FormStoreStrategy::RELATION_SINGLE_MORPH, $resolver->determineFormStoreStrategy($data));
    }

    /**
     * @test
     */
    function it_falls_back_to_null_for_form_store_strategy()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type' => 'unknown',
            'translated' => false,
        ]);
        static::assertNull($resolver->determineFormStoreStrategy($data));
    }

    // ------------------------------------------------------------------------------
    //      Form Store Options
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_determines_form_store_options_for_morph_to_relation()
    {
        $resolver = new RelationStrategyResolver;

        $data = new ModelRelationData([
            'type'        => RelationType::MORPH_TO,
            'morphModels' => ['Some\\TestModel', 'Some\\OtherModel'],
        ]);
        static::assertEquals(
            ['models' => ['Some\\TestModel', 'Some\\OtherModel']],
            $resolver->determineFormStoreOptions($data)
        );

        $data = new ModelRelationData([
            'type'        => RelationType::MORPH_TO,
            'morphModels' => null,
        ]);
        static::assertEquals(
            [],
            $resolver->determineFormStoreOptions($data)
        );
    }

}
