<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Czim\CmsModels\Test\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleCollectionInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\Support\Validation\ValidationRuleMerger;
use Czim\CmsModels\Test\TestCase;

class ValidationRuleMergerTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_empty_collection_for_empty_input()
    {
        $merger = new ValidationRuleMerger;

        $collection = $merger->mergeStrategyAndAttributeBased([], []);

        static::assertInstanceOf(ValidationRuleCollectionInterface::class, $collection);
        static::assertTrue($collection->isEmpty());
    }

    /**
     * @test
     */
    function it_returns_model_information_based_rules_when_no_strategy_rules_were_known()
    {
        $merger = new ValidationRuleMerger;

        $collection = $merger->mergeStrategyAndAttributeBased([], [
            'test' => [ 'nullable', 'string' ],
            'required',
        ]);

        static::assertInstanceOf(ValidationRuleCollectionInterface::class, $collection);
        static::assertCount(2, $collection);

        /** @var ValidationRuleDataInterface $item */
        $item = $collection->first();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEquals('test', $item->key());
        static::assertEquals(['nullable', 'string'], $item->rules());

        $item = $collection->last();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['required'], $item->rules());
    }

    /**
     * @test
     */
    function it_returns_strategy_rules_unchanged_when_no_model_information_based_rules_available()
    {
        $merger = new ValidationRuleMerger;

        $collection = $merger->mergeStrategyAndAttributeBased([
            'test' => [ 'nullable', 'string' ],
            'required',
        ], []);

        static::assertInstanceOf(ValidationRuleCollectionInterface::class, $collection);
        static::assertCount(2, $collection);

        /** @var ValidationRuleDataInterface $item */
        $item = $collection->first();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEquals('test', $item->key());
        static::assertEquals(['nullable', 'string'], $item->rules());

        $item = $collection->last();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['required'], $item->rules());
    }

    /**
     * @test
     */
    function it_merges_strategy_and_information_based_rules_without_overlap()
    {
        $merger = new ValidationRuleMerger;

        $collection = $merger->mergeStrategyAndAttributeBased([
            'nullable', 'string',
        ], [
            'required',
        ]);

        static::assertInstanceOf(ValidationRuleCollectionInterface::class, $collection);
        static::assertCount(3, $collection);

        /** @var ValidationRuleDataInterface $item */
        $item = $collection->first();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['nullable'], $item->rules());

        $item = $collection->offsetGet(1);
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['string'], $item->rules());

        $item = $collection->last();
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['required'], $item->rules());
    }

}
