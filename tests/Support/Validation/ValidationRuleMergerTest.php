<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Czim\CmsModels\Test\Support\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleCollectionInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\ModelInformation\Data\Form\Validation\ValidationRuleData;
use Czim\CmsModels\Support\Validation\ValidationRuleMerger;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ValidationRuleMergerTest
 *
 * @uses \Czim\CmsModels\ModelInformation\Data\Form\Validation\ValidationRuleData
 */
class ValidationRuleMergerTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_empty_results_for_empty_input()
    {
        $merger = new ValidationRuleMerger;

        $rules = $merger->mergeStrategyAndAttributeBased([], []);

        static::assertInternalType('array', $rules);
        static::assertCount(0, $rules);
    }

    /**
     * @test
     */
    function it_returns_model_information_based_rules_when_no_strategy_rules_were_known()
    {
        $merger = new ValidationRuleMerger;

        $merged = $merger->mergeStrategyAndAttributeBased([], [
            new ValidationRuleData(['nullable', 'string'], 'test'),
            new ValidationRuleData(['required']),
        ]);

        static::assertInternalType('array', $merged);
        static::assertCount(2, $merged);

        /** @var ValidationRuleDataInterface $item */
        $item = $merged[0];
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEquals('test', $item->key());
        static::assertEquals(['nullable', 'string'], $item->rules());

        $item = $merged[1];
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

        $merged = $merger->mergeStrategyAndAttributeBased([
            new ValidationRuleData(['nullable', 'string'], 'test'),
            new ValidationRuleData(['required']),
        ], []);

        static::assertInternalType('array', $merged);
        static::assertCount(2, $merged);

        /** @var ValidationRuleDataInterface $item */
        $item = $merged[0];
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEquals('test', $item->key());
        static::assertEquals(['nullable', 'string'], $item->rules());

        $item = $merged[1];
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['required'], $item->rules());
    }

    /**
     * @test
     */
    function it_merges_inheritable_information_based_rules_into_strategy_rules_without_overlap_for_empty_key()
    {
        $merger = new ValidationRuleMerger;

        $merged = $merger->mergeStrategyAndAttributeBased([
            new ValidationRuleData(['required', 'string']),
        ], [
            new ValidationRuleData(['filled']),
        ]);

        static::assertInternalType('array', $merged);
        static::assertCount(1, $merged);

        /** @var ValidationRuleDataInterface $item */
        $item = $merged[0];
        static::assertInstanceOf(ValidationRuleDataInterface::class, $item);
        static::assertEmpty($item->key());
        static::assertEquals(['required', 'string', 'filled'], $item->rules());
    }

}
