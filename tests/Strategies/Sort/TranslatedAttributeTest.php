<?php
namespace Czim\CmsModels\Test\Strategies\Sort;

use Czim\CmsModels\Strategies\Sort\TranslatedAttribute;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class TranslatedAttributeTest
 *
 * @group strategies
 * @group strategies-sort
 */
class TranslatedAttributeTest extends AbstractPostCommentSeededTestCase
{

    /**
     * @test
     */
    function it_orders_a_model_query_for_a_translated_attribute()
    {
        $strategy = new TranslatedAttribute;

        // title ordered
        $query = TestPost::query();
        $query = $strategy->apply($query, 'title');
        $results = $query->get();
        static::assertEquals([2,1,3], $results->pluck('id')->toArray(), "Incorrect models order for title");

        // body sorted
        $query = TestPost::query();
        $query = $strategy->apply($query, 'body');
        $results = $query->get();
        static::assertEquals([3,2,1], $results->pluck('id')->toArray(), "Incorrect models order for body");
    }
    
    /**
     * @test
     */
    function it_uses_fallback_locale_only_if_configured_to()
    {
        // Set up and test for fallback locale (= 'en')
        app()->setLocale('nl');

        $strategy = new TranslatedAttribute;

        $query = TestPost::query();
        $query = $strategy->apply($query, 'title');

        static::assertRegExp(
            '#\(\s*[\'"`]?locale[\'"`]?\s*=\s*\?\s*or\s*[\'"`]?locale[\'"`]?\s*=\s*\?\s*\)#i',
            $query->toSql(),
            'There should be a bracketed locale x or y condition'
        );

        $bindings = $query->getBindings();
        sort($bindings);

        static::assertEquals(['en', 'nl'], $bindings, 'The locale bindings do not match');
        

        // Set up and test when fallback is disabled
        $this->app['config']->set('translatable.use_fallback', false);

        $query = TestPost::query();
        $query = $strategy->apply($query, 'title');

        static::assertNotRegExp(
            '#\(\s*[\'"`]?locale[\'"`]?\s*=\s*\?\s*or\s*[\'"`]?locale[\'"`]?\s*=\s*\?\s*\)#i',
            $query->toSql(),
            'There should be not bracketed locale x or y condition'
        );
    }
    
    /**
     * @test
     */
    function it_uses_null_last_sorting_using_if_only_when_connection_supports_it()
    {
        // It should not be used when connecting with SQLite
        $strategy = new TranslatedAttribute;

        $query = TestPost::query();
        $query = $strategy->apply($query, 'title');

        static::assertNotRegExp('#order by IF\(#', $query->toSql(), 'There should be no IF() clause for SQLite');


        // It should be used when connecting with MySQL
        $this->setConfigForMysql();

        $strategy = new TranslatedAttribute;

        $query = TestPost::query();
        $query = $strategy->apply($query, 'title');

        static::assertRegExp(
            '#order by IF\((`[a-z0-9_-]+`\.)?`title` IS NULL OR (`[a-z0-9_-]+`\.)?`title` = \'\',1,0\) asc#',
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
