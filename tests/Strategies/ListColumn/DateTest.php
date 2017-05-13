<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Strategies\ListColumn\Date;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use DateTime;

/**
 * Class DateTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class DateTest extends TestCase
{

    /**
     * @test
     */
    function it_renders_a_date_value_in_the_default_format()
    {
        $strategy = new Date;

        $model = new TestPost;
        $model->created_at = '2017-01-01 13:05:00';

        static::assertEquals('2017-01-01', $strategy->render($model, 'created_at'));

        $model->test = '2017-01-01 13:05:00';
        static::assertEquals('2017-01-01', $strategy->render($model, 'test'));

        $model->test = (new DateTime('2017-01-01'))->getTimestamp();
        static::assertEquals('2017-01-01', $strategy->render($model, 'test'));
    }

    /**
     * @test
     */
    function it_renders_a_date_value_in_a_custom_format()
    {
        $strategy = new Date;
        $strategy->setOptions(['format' => 'Y H:i']);

        $model = new TestPost;
        $model->created_at = '2017-01-01 13:05:00';

        static::assertEquals('2017 13:05', $strategy->render($model, 'created_at'));
    }

    /**
     * @test
     */
    function it_renders_an_empty_value_as_empty_string()
    {
        $strategy = new Date;
        $model    = new TestPost;

        $model->test = null;
        static::assertEquals('', $strategy->render($model, 'test'));

        $model->test = '';
        static::assertEquals('', $strategy->render($model, 'test'));
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_attempting_to_render_a_value_uninterpretable_as_a_date()
    {
        $strategy = new Date;
        $model    = new TestPost;

        $model->test = false;

        $strategy->render($model, 'test');
    }
    
    /**
     * @test
     */
    function it_returns_center_style()
    {
        $strategy = new Date;

        static::assertEquals('column-center', $strategy->style(new TestPost, 'created_at'));
    }

}
