<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\Strategies\ListColumn\DefaultStrategy;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use DateTime;

/**
 * Class DefaultTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class DefaultTest extends TestCase
{

    /**
     * @test
     */
    function it_renders_a_value_as_is()
    {
        $strategy = new DefaultStrategy;

        $model = new TestPost;
        $model->test = 'string value';

        static::assertEquals('string value', $strategy->render($model, 'test'));

        $model->test = 13;
        static::assertEquals('13', $strategy->render($model, 'test'));
    }

    /**
     * @test
     */
    function it_returns_empty_attributes()
    {
        $strategy = new DefaultStrategy;

        static::assertEquals([], $strategy->attributes(new TestPost, 'title'));
    }

    /**
     * @test
     */
    function it_returns_style_based_on_attribute_data()
    {
        $strategy = new DefaultStrategy;

        // No data set
        static::assertNull($strategy->style(new TestPost, 'test'));

        // Float / Integer cast
        $strategy->setAttributeInformation(new ModelAttributeData([
            'cast' => AttributeCast::INTEGER,
        ]));
        static::assertEquals('column-right', $strategy->style(new TestPost, 'test'));

        $strategy->setAttributeInformation(new ModelAttributeData([
            'cast' => AttributeCast::FLOAT,
        ]));
        static::assertEquals('column-right', $strategy->style(new TestPost, 'test'));

        // Date
        $strategy->setAttributeInformation(new ModelAttributeData([
            'cast' => AttributeCast::DATE,
        ]));
        static::assertEquals('column-date', $strategy->style(new TestPost, 'test'));

        // Fallback to null
        $strategy->setAttributeInformation(new ModelAttributeData([
            'cast' => AttributeCast::STRING,
        ]));
        static::assertNull($strategy->style(new TestPost, 'test'));
    }

    /**
     * @test
     */
    function it_sets_and_returns_options()
    {
        $strategy = new DefaultStrategy;

        static::assertSame($strategy, $strategy->setOptions(['test' => 'a']));

        static::assertEquals(['test' => 'a'], $strategy->options());
    }

    /**
     * @test
     */
    function it_returns_options_from_list_column_data_as_fallback()
    {
        $strategy = new DefaultStrategy;

        $strategy->setListInformation(new ModelListColumnData([
            'options' => ['test' => 'b'],
        ]));

        static::assertEquals(['test' => 'b'], $strategy->options());
    }

    /**
     * @test
     */
    function it_initializes_the_strategy()
    {
        $strategy = new DefaultStrategy;

        static::assertSame($strategy, $strategy->initialize(TestPost::class));
    }

}
