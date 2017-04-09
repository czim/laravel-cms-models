<?php
namespace Czim\CmsModels\Test\Support\Session;

use Czim\CmsModels\Support\Session\ModelListMemory;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelListMemoryTest
 *
 * @group support
 * @group support-helpers
 */
class ModelListMemoryTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_and_returns_the_context_key()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getContext());

        $memory->setContext('testing');

        static::assertEquals('testing', $memory->getContext());
    }

    /**
     * @test
     */
    function it_sets_and_returns_the_secondary_context_key()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getSubContext());

        $memory->setSubContext('testing');

        static::assertEquals('testing', $memory->getSubContext());
    }

    /**
     * @test
     */
    function it_returns_whether_filters_are_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasFilters());

        $memory->setFilters(['test' => 'value']);

        static::assertTrue($memory->hasFilters());

        $memory->clearFilters();

        static::assertFalse($memory->hasFilters());
    }

    /**
     * @test
     */
    function it_sets_and_returns_filters()
    {
        $memory = new ModelListMemory;

        static::assertEquals([], $memory->getFilters());

        $memory->setFilters(['test' => 'value']);

        static::assertEquals(['test' => 'value'], $memory->getFilters());

        $memory->setFilters([]);

        static::assertEquals([], $memory->getFilters());
    }

    /**
     * @test
     */
    function it_returns_whether_sort_data_is_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasSortData());

        $memory->setSortData('test.column', 'desc');

        static::assertTrue($memory->hasSortData());

        $memory->clearSortData();

        static::assertFalse($memory->hasSortData());
    }

    /**
     * @test
     */
    function it_sets_and_returns_sort_data()
    {
        $memory = new ModelListMemory;

        static::assertEquals([], $memory->getSortData());

        $memory->setSortData('test.column', 'desc');

        static::assertEquals(['column' => 'test.column', 'direction' => 'desc'], $memory->getSortData());

        $memory->setSortData(null);

        static::assertEquals([], $memory->getSortData());
    }

    /**
     * @test
     */
    function it_returns_whether_page_is_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasPage());

        $memory->setPage(13);

        static::assertTrue($memory->hasPage());

        $memory->clearPage();

        static::assertFalse($memory->hasPage());
    }

    /**
     * @test
     */
    function it_sets_and_returns_page()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getPage());

        $memory->setPage(13);

        static::assertEquals(13, $memory->getPage());

        $memory->setPage(null);

        static::assertNull($memory->getPage());
    }

    /**
     * @test
     */
    function it_returns_whether_page_size_is_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasPageSize());

        $memory->setPageSize(100);

        static::assertTrue($memory->hasPageSize());

        $memory->clearPageSize();

        static::assertFalse($memory->hasPageSize());
    }

    /**
     * @test
     */
    function it_sets_and_returns_page_size()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getPageSize());

        $memory->setPageSize(100);

        static::assertEquals(100, $memory->getPageSize());

        $memory->setPageSize(null);

        static::assertNull($memory->getPageSize());
    }

    /**
     * @test
     */
    function it_returns_whether_scope_is_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasScope());

        $memory->setScope('testing');

        static::assertTrue($memory->hasScope());

        $memory->clearScope();

        static::assertFalse($memory->hasScope());
    }

    /**
     * @test
     */
    function it_sets_and_returns_scope()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getScope());

        $memory->setScope('testing');

        static::assertEquals('testing', $memory->getScope());

        $memory->setScope(null);

        static::assertNull($memory->getScope());
    }

    /**
     * @test
     */
    function it_returns_whether_list_parents_are_set()
    {
        $memory = new ModelListMemory;

        static::assertFalse($memory->hasListParent());

        $memory->setListParent('testing', 13);

        static::assertTrue($memory->hasListParent());

        $memory->clearListParent();

        static::assertFalse($memory->hasListParent());
    }

    /**
     * @test
     */
    function it_sets_and_returns_list_parents()
    {
        $memory = new ModelListMemory;

        static::assertNull($memory->getListParent());

        $memory->setListParent('testing', 13);

        static::assertEquals(['relation' => 'testing', 'key' => 13], $memory->getListParent());

        $memory->setListParent(null);

        static::assertNull($memory->getListParent());

        $memory->setListParent(false);

        static::assertFalse($memory->getListParent());
    }

}
