<?php
namespace Czim\CmsModels\Test\View\FilterStrategies;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\View\FilterStrategies\DropdownBoolean;
use Illuminate\Contracts\View\View;
use Mockery;

class DropdownBooleanTest extends AbstractFilterStrategyTestCase
{

    /**
     * @test
     */
    function it_renders_an_input_field()
    {
        $core = $this->getMockCore();
        $core->shouldReceive('config')->andReturnUsing(function ($key, $default = null) {
            return $default;
        });
        $this->app->instance(Component::CORE, $core);

        $strategy = $this->makeFilterStrategy();

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#name="filter\[test\]"#', $render);
        static::assertRegExp('#option value="1"#', $render);
        static::assertRegExp('#option value="0"#', $render);
    }

    /**
     * @test
     */
    function it_renders_options_using_filter_data_with_options_labels()
    {
        $core = $this->getMockCore();
        $core->shouldReceive('config')->andReturnUsing(function ($key, $default = null) {
            return $default;
        });
        $this->app->instance(Component::CORE, $core);

        $info = new ModelListFilterData([
            'options' => [
                'false_label' => 'test false',
                'true_label'  => 'test true',
            ],
        ]);

        $strategy = $this->makeFilterStrategy();
        $strategy->setFilterInformation($info);

        $view = $strategy->render('test', '');

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#name="filter\[test\]"#', $render);
        static::assertRegExp('#<option[^>]*>\s*test true\s*</#sm', $render);
        static::assertRegExp('#<option[^>]*>\s*test false\s*</#sm', $render);
    }

    /**
     * @test
     */
    function it_renders_options_using_filter_data_with_translated_options()
    {
        $core = $this->getMockCore();
        $core->shouldReceive('config')->andReturnUsing(function ($key, $default = null) {
            return $default;
        });
        $this->app->instance(Component::CORE, $core);

        // Set translation key
        $this->app->setLocale('en');
        $this->app['translator']->addLines([
            'test.false' => 'false translated',
            'test.true'  => 'true translated',
        ], 'en', 'cms');

        $info = new ModelListFilterData([
            'options' => [
                'false_label_translated' => 'test.false',
                'true_label_translated'  => 'test.true',
            ],
        ]);

        $strategy = $this->makeFilterStrategy();
        $strategy->setFilterInformation($info);

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#name="filter\[test\]"#', $render);
        static::assertRegExp('#<option[^>]*>\s*true translated\s*</#sm', $render);
        static::assertRegExp('#<option[^>]*>\s*false translated\s*</#sm', $render);
    }


    // ------------------------------------------------------------------------------
    //      Apply
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_filters_on_a_single_direct_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'checked', true);

        static::assertEquals(2, $query->count());
        static::assertEquals([1, 3], $query->pluck('id')->toArray());

        // Make sure we get no hits when we shouldn't
        $query = $this->getPostQuery();
        $strategy->apply($query, 'checked', false);
        static::assertEquals(1, $query->count());
        static::assertEquals(2, $query->first()['id']);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getPostQuery()
    {
        return TestPost::query();
    }

    /**
     * @return FilterStrategyInterface
     */
    protected function makeFilterStrategy()
    {
        return new DropdownBoolean;
    }

    /**
     * @return CoreInterface|\Mockery\MockInterface|\Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

    /**
     * Binds mock repository with mocked information for the test post model.
     */
    protected function bindMockModelRepositoryForPostModel()
    {
        $this->app->bind(ModelInformationRepositoryInterface::class, function () {
            return $this->getMockModelRepository();
        });
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModelInformationRepositoryInterface
     */
    protected function getMockModelRepository()
    {
        $mock = $this->getMockBuilder(ModelInformationRepositoryInterface::class)->getMock();

        $mock->method('getByModel')
            ->with(static::isInstanceOf(TestPost::class))
            ->willReturn(
                new ModelInformation([
                    'attributes' => [
                        'title' => new ModelAttributeData([
                            'type'       => 'varchar',
                            'cast'       => 'string',
                            'translated' => true,
                        ]),
                        'body' => new ModelAttributeData([
                            'type'       => 'text',
                            'cast'       => 'string',
                            'translated' => true,
                        ]),
                        'description' => new ModelAttributeData([
                            'type'       => 'varchar',
                            'cast'       => 'string',
                        ]),
                    ],
                ])
            );

        return $mock;
    }
}
