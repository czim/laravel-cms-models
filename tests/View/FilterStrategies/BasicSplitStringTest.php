<?php
namespace Czim\CmsModels\Test\View\FilterStrategies;

use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\View\FilterStrategies\BasicSplitString;

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

        $this->assertEquals(1, $query->count());
        $this->assertEquals(1, $query->first()->id);

        // Make sure we get no hits when we shouldn't
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'doesnotmatchanythingatall andthisdoesnteither');
        $this->assertEquals(0, $query->count());

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'e testing');
        $this->assertEquals(3, $query->count());
    }
    
    /**
     * @test
     */
    function it_filters_on_split_terms_on_a_single_translated_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'title', 'alternative elaborate');

        $this->assertEquals(1, $query->count());
        $this->assertEquals(2, $query->first()->id);

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'title', 'title e');
        $this->assertEquals(3, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_multiple_comma_separated_different_target_attributes()
    {
        $strategy = $this->makeFilterStrategy();

        $query = $this->getPostQuery();
        $strategy->apply($query, 'description,title', 'hopscotch title');
        $this->assertEquals(3, $query->count());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,description', 'testing party-tent');
        $this->assertEquals(2, $query->count());
        $this->assertEquals([1, 2], $query->pluck('id')->toArray());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,author.name,description', 'tortellini tosti');
        $this->assertEquals(1, $query->count());
        $this->assertEquals(3, $query->first()->id);
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
