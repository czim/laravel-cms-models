<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Strategies\ListColumn\TagList;
use Czim\CmsModels\Test\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Class TagListTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class TagListTest extends TestCase
{

    /**
     * @test
     */
    function it_renders_a_list_of_tags_as_a_string()
    {
        $strategy = new TagList;

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);

        $model->shouldReceive('tagNames')->andReturn(['tag-A', 'tag-B']);

        static::assertEquals('tag-A, tag-B', $strategy->render($model, 'tags'));
    }

}
