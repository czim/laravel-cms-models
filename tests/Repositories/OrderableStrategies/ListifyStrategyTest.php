<?php
namespace Czim\CmsModels\Test\Repositories\OrderableStrategies;

use Czim\CmsModels\Repositories\OrderableStrategies\ListifyStrategy;
use Czim\CmsModels\Support\Enums\OrderablePosition;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOrderable;
use Illuminate\Support\Facades\Schema;
use Mockery;

/**
 * Class ListifyStrategyTest
 *
 * @group repository
 * @group repository-strategy
 */
class ListifyStrategyTest extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::create('test_orderables', function($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments('id');
            $table->string('name', 255);
            $table->integer('position')->nullable();
            $table->nullableTimestamps();
        });
    }

    protected function seedDatabase()
    {
        TestOrderable::create([
            'name'     => 'a',
            'position' => 1,
        ]);

        TestOrderable::create([
            'name'     => 'b',
            'position' => 2,
        ]);

        TestOrderable::create([
            'name'     => 'c',
            'position' => 3,
        ]);

        TestOrderable::create([
            'name'     => 'd',
            'position' => 4,
        ]);
    }

    /**
     * @test
     */
    function it_sets_the_position()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, 3);

        static::assertEquals(
            [1, 3, 2, 4],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_does_not_update_the_position_if_it_is_already_correct()
    {
        /** @var TestOrderable|Mockery\Mock $model */
        $model = Mockery::mock(TestOrderable::class);

        $model->shouldReceive('getListifyPosition')->andReturn(2);
        $model->shouldReceive('insertAt')->never();

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, 2);
    }

    /**
     * @test
     */
    function it_removes_the_record_from_a_list()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::REMOVE);

        static::assertEquals(
            [1, 3, 4],
            TestOrderable::orderBy('position')->whereNotNull('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_moves_the_record_one_position_up()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::UP);

        static::assertEquals(
            [2, 1, 3, 4],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_moves_the_record_one_position_down()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::DOWN);

        static::assertEquals(
            [1, 3, 2, 4],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_moves_the_record_to_the_top()
    {
        $model = TestOrderable::find(3);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::TOP);

        static::assertEquals(
            [3, 1, 2, 4],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_moves_the_record_to_the_top_if_it_was_not_in_the_list()
    {
        $model = TestOrderable::find(3);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::REMOVE);
        $strategy->setPosition($model, OrderablePosition::TOP);

        static::assertEquals(
            [3, 1, 2, 4],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     */
    function it_moves_the_record_to_the_bottom()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::BOTTOM);

        static::assertEquals(
            [1, 3, 4, 2],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

    /**
     * @test
     * @depends it_removes_the_record_from_a_list
     */
    function it_moves_the_record_to_the_bottom_if_it_was_not_in_the_list()
    {
        $model = TestOrderable::find(2);

        $strategy = new ListifyStrategy;

        $strategy->setPosition($model, OrderablePosition::REMOVE);
        $strategy->setPosition($model, OrderablePosition::BOTTOM);

        static::assertEquals(
            [1, 3, 4, 2],
            TestOrderable::orderBy('position')->pluck('id')->toArray()
        );
    }

}
