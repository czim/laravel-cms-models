<?php
namespace Czim\CmsModels\Test\Strategies\Filter;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListFilterData;
use Czim\CmsModels\Strategies\Filter\Datepicker;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

class DatepickerTest extends AbstractFilterStrategyTestCase
{

    /**
     * @test
     */
    function it_renders_a_datepicker_field()
    {
        $core = $this->getMockCore();

        $this->app->instance(Component::CORE, $core);

        $strategy = new Datepicker;

        $view = $strategy->render('test', true);

        static::assertInstanceOf(View::class, $view);

        $render = $view->render();
        static::assertRegExp('#name="filter\[test\]"#', $render);
        static::assertRegExp('#id="__filter_datepicker__test"#i', $render);
    }


    // ------------------------------------------------------------------------------
    //      Apply
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_filters_for_a_datetime_value()
    {
        $strategy = new Datepicker;

        $model = new TestPost;

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('getModel')->andReturn($model);

        $queryMock->shouldReceive('where')
            ->with('date_column', '>=', Mockery::on(function ($time) {
                if ( ! ($time instanceof \DateTime)) {
                    return false;
                }
                return $time->format('Y-m-d H:i:s') == '2017-01-01 00:00:00';
            }))
            ->once()->andReturnSelf();

        $queryMock->shouldReceive('where')
            ->with('date_column', '<=', Mockery::on(function ($time) {
                if ( ! ($time instanceof \DateTime)) {
                    return false;
                }
                return $time->format('Y-m-d H:i:s') == '2017-01-01 23:59:59';
            }))
            ->once()->andReturnSelf();

        $strategy->apply($queryMock, 'date_column', '2017-01-01 15:00:00', []);
    }

    /**
     * @test
     */
    function it_filters_for_a_date_value()
    {
        $strategy = new Datepicker;

        $strategy->setFilterInformation(new ModelListFilterData([
            'target'  => 'date_column',
            'options' => [
                'format' => 'Y-m-d',
            ],
        ]));

        $model = new TestPost;

        $info = new ModelInformation([
            'attributes' => [
                'date_column' => [
                    'type' => 'date',
                ],
            ],
        ]);

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $repositoryMock */
        $repositoryMock = Mockery::mock(ModelInformationRepositoryInterface::class);
        $repositoryMock->shouldReceive('getByModel')->with($model)->andReturn($info);
        $this->app->instance(ModelInformationRepositoryInterface::class, $repositoryMock);

        /** @var Builder|Mockery\Mock $queryMock */
        $queryMock = Mockery::mock(Builder::class);

        $queryMock->shouldReceive('getModel')->andReturn($model);

        $queryMock->shouldReceive('where')->with('date_column', '=', '2017-01-01')->once()->andReturnSelf();

        $strategy->apply($queryMock, 'date_column', '2017-01-01', []);
    }


    /**
     * @return CoreInterface|\Mockery\MockInterface|\Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
