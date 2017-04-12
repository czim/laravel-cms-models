<?php
namespace Czim\CmsModels\Test\Strategies\Filter;

use Czim\CmsModels\Contracts\Strategies\FilterStrategyInterface;
use Czim\CmsModels\Strategies\Filter\BasicSplitString;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

/**
 * Class BasicSplitStringTest
 *
 * @group strategies
 * @group strategies-filter
 */
class BasicSplitStringTest extends AbstractFilterStrategyTestCase
{

    /**
     * @test
     */
    function it_filters_on_split_terms_on_a_single_direct_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'description', 'possible best');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // Make sure we get no hits when we shouldn't
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'doesnotmatchanythingatall andthisdoesnteither');
        static::assertEquals(0, $query->count());

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'e testing');
        static::assertEquals(2, $query->count());
    }
    
    /**
     * @test
     */
    function it_filters_on_split_terms_on_a_single_translated_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'title', 'alternative elaborate');

        static::assertEquals(1, $query->count());
        static::assertEquals(2, $query->first()['id']);

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'title', 'title e');
        static::assertEquals(3, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_multiple_comma_separated_different_target_attributes()
    {
        $strategy = $this->makeFilterStrategy();

        $query = $this->getPostQuery();
        $strategy->apply($query, 'description,title', 'hopscotch title testing');
        static::assertEquals(0, $query->count());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,description', 'testing post');
        static::assertEquals(2, $query->count());
        static::assertEquals([1, 2], $query->pluck('id')->toArray());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,author.name,description', 'tortellini tosti');
        static::assertEquals(2, $query->count());
        static::assertEquals([2, 3], $query->pluck('id')->toArray());
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
        return new BasicSplitString();
    }

}
