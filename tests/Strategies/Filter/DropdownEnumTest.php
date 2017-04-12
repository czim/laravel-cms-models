<?php
namespace Czim\CmsModels\Test\Strategies\Filter;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\Strategies\Filter\DropdownEnum;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Strategies\Dropdown\TestEnum;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

/**
 * Class DropdownEnumTest
 *
 * @group strategies
 * @group strategies-filter
 */
class DropdownEnumTest extends AbstractFilterStrategyTestCase
{

    /**
     * @test
     */
    function it_renders_a_dropdown_field_with_options_defined_in_config()
    {
        $core = $this->getMockCore();
        $this->app->instance(Component::CORE, $core);

        $data = new ModelListFilterData([
            'target' => 'enum_column',
            'options' => [
                'labels' => [
                    'a' => 'Label A',
                    'b' => 'Label B',
                ],
                'values' => [
                    'a',
                    'b',
                    'c',
                ],
            ],
        ]);

        $strategy = new DropdownEnum;
        $strategy->setFilterInformation($data);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#name="filter\[test\]"#', $render);
        static::assertRegExp('#option value="a"[^<]+Label A#i', $render);
        static::assertRegExp('#option value="b"[^<]+Label B#i', $render);
        static::assertRegExp('#option value="c"[^<]+c#i', $render);
    }

    /**
     * @test
     */
    function it_renders_a_dropdown_field_with_translated_options_defined_in_config()
    {
        $core = $this->getMockCore();
        $this->app->instance(Component::CORE, $core);

        $data = new ModelListFilterData([
            'target' => 'enum_column',
            'options' => [
                'labels_translated' => [
                    'a' => 'label.key.a',
                    'b' => 'label.key.b',
                ],
                'values' => [
                    'a',
                    'b',
                ],
            ],
        ]);

        $core->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app['translator']->addLines(['label.key.a' => 'Label A', 'label.key.b' => 'Label B'], 'en', '*');

        $strategy = new DropdownEnum;
        $strategy->setFilterInformation($data);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#option value="a"[^<]+Label A#i', $render);
        static::assertRegExp('#option value="b"[^<]+Label B#i', $render);
    }

    /**
     * @test
     */
    function it_renders_a_dropdown_field_with_label_and_value_source_class_defined_in_config()
    {
        $core = $this->getMockCore();
        $this->app->instance(Component::CORE, $core);

        $data = new ModelListFilterData([
            'target' => 'enum_column',
            'options' => [
                'label_source' => TestEnum::class,
                'value_source' => TestEnum::class,
            ],
        ]);

        $strategy = new DropdownEnum;
        $strategy->setFilterInformation($data);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#option value="a"[^<]+Label A#i', $render);
        static::assertRegExp('#option value="b"[^<]+Label B#i', $render);
        static::assertRegExp('#option value="c"[^<]+c#i', $render, 'It does not fall back to value for missing label');
    }

    /**
     * @test
     */
    function it_renders_a_dropdown_field_with_enum_as_value_source_class_defined_in_config()
    {
        $core = $this->getMockCore();
        $this->app->instance(Component::CORE, $core);

        $data = new ModelListFilterData([
            'target' => 'enum_column',
            'options' => [
                'value_source' => Component::class,
            ],
        ]);

        $strategy = new DropdownEnum;
        $strategy->setFilterInformation($data);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#option value="cms-core"#i', $render);
    }

    /**
     * @test
     */
    function it_silently_ignores_when_an_indicated_value_or_label_source_is_invalid()
    {
        $core = $this->getMockCore();
        $this->app->instance(Component::CORE, $core);

        $data = new ModelListFilterData([
            'target' => 'enum_column',
            'options' => [
                'label_source' => static::class,
                'value_source' => static::class,
            ],
        ]);

        $strategy = new DropdownEnum;
        $strategy->setFilterInformation($data);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $view->render();
    }


    // ------------------------------------------------------------------------------
    //      Apply
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_filters_for_a_single_value()
    {
        $strategy = new DropdownEnum;

        $model = new TestPost;

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('getModel')->andReturn($model);

        $queryMock->shouldReceive('where')->with('enum_column', '=', 'picked-value', Mockery::any())->once()->andReturnSelf();

        $strategy->apply($queryMock, 'enum_column', 'picked-value', []);
    }

    /**
     * @test
     */
    function it_filters_for_list_of_values_with_wherein()
    {
        $strategy = new DropdownEnum;

        $model = new TestPost;

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('getModel')->andReturn($model);

        $queryMock->shouldReceive('whereIn')
            ->with('enum_column', ['picked-value', 'another'], Mockery::any())
            ->once()->andReturnSelf();

        $strategy->apply($queryMock, 'enum_column', ['picked-value', 'another'], []);
    }


    /**
     * @return CoreInterface|\Mockery\MockInterface|\Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
