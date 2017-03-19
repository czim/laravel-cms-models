<?php
namespace Czim\CmsModels\Test\Repositories\SortStrategies;

use Czim\CmsModels\Repositories\SortStrategies\TranslatedAttribute;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

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

}
