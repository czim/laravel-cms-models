<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Strategies\ListColumn\Check;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class CheckTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class CheckTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        /** @var CoreInterface|Mockery\Mock $mockCore */
        $mockCore = Mockery::mock(CoreInterface::class);
        $mockCore->shouldReceive('config')->andReturnUsing(function() {
            return func_get_arg(1);
        });

        $this->app->instance(Component::CORE, $mockCore);
    }

    /**
     * @test
     */
    function it_renders_a_value_as_a_boolean()
    {
        $strategy = new Check;

        $model = new TestPost;

        $trueString  = '<i class="fa fa-check text-success" title="' . e(cms_trans('common.boolean.true')) . '"></i>';
        $falseString = '<i class="fa fa-times text-danger" title="' . e(cms_trans('common.boolean.false')) . '"></i>';

        $model->test = true;
        static::assertEquals($trueString, $strategy->render($model, 'test'));

        $model->test = false;
        static::assertEquals($falseString, $strategy->render($model, 'test'));

        $model->test = 1;
        static::assertEquals($trueString, $strategy->render($model, 'test'));

        $model->test = 0;
        static::assertEquals($falseString, $strategy->render($model, 'test'));

        $model->test = 'true';
        static::assertEquals($trueString, $strategy->render($model, 'test'));

        $model->test = 'false';
        static::assertEquals($falseString, $strategy->render($model, 'test'));

        $model->test = [1];
        static::assertEquals($trueString, $strategy->render($model, 'test'));

        $model->test = [];
        static::assertEquals($falseString, $strategy->render($model, 'test'));

        $model->test = (object) [true];
        static::assertEquals($trueString, $strategy->render($model, 'test'));
    }

    /**
     * @test
     */
    function it_renders_null_value_as_false()
    {
        $strategy = new Check;

        $model = new TestPost;

        $model->test = null;

        static::assertEquals(
            '<i class="fa fa-times text-danger" title="' . e(cms_trans('common.boolean.false')) . '"></i>',
            $strategy->render($model, 'test')
        );
    }

    /**
     * @test
     */
    function it_returns_center_style()
    {
        $strategy = new Check;

        static::assertEquals('column-center', $strategy->style(new TestPost, 'created_at'));
    }

}
