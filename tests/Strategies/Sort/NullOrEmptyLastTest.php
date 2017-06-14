<?php
namespace Czim\CmsModels\Test\Strategies\Sort;

use Czim\CmsModels\Strategies\Sort\NullOrEmptyLast;
use Czim\CmsModels\Test\DatabaseTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class NullOrEmptyLastTest
 *
 * @group strategies
 * @group strategies-sort
 */
class NullOrEmptyLastTest extends DatabaseTestCase
{

    /**
     * @test
     */
    function it_orders_a_model_query_normally_if_using_if_is_not_supported()
    {
        $strategy = new NullOrEmptyLast;

        // title ordered
        $query = $strategy->apply(new TestPost, 'type');

        static::assertNotRegExp('#order by IF\(#', $query->toSql(), 'There should be no IF() clause for SQLite');
    }
    
    /**
     * @test
     */
    function it_uses_null_last_sorting_using_if_connection_supports_it()
    {
        $this->setConfigForMysql();

        $strategy = new NullOrEmptyLast;

        $query = TestPost::query();
        $query = $strategy->apply($query, 'type');

        static::assertRegExp(
            '#order by IF\((`[a-z0-9_-]+`\.)?`type` IS NULL OR (`[a-z0-9_-]+`\.)?`type` = \'\',1,0\) asc#',
            $query->toSql(),
            'No null-sorting IF() clause found in query'
        );
    }

    protected function setConfigForMysql()
    {
        $this->app['config']->set('database.default', 'testbench_mysql');
        $this->app['config']->set('database.connections.testbench_mysql', [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'testing',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
    }

}
