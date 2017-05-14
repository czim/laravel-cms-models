<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Codesleeve\Stapler\Attachment;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Strategies\ListColumn\StaplerFile;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Mockery;

/**
 * Class StaplerFileTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class StaplerFileTest extends AbstractPostCommentSeededTestCase
{

    public function setUp()
    {
        parent::setUp();

        /** @var ModelInformationRepositoryInterface|Mockery\Mock $mockRepository */
        $mockRepository = Mockery::mock(ModelInformationRepositoryInterface::class);
        $mockRepository->shouldReceive('getByModel')->andReturn(null);

        $this->app->instance(ModelInformationRepositoryInterface::class, $mockRepository);
    }

    /**
     * @test
     */
    function it_renders_a_stapler_attachment_as_a_link()
    {
        $strategy = new StaplerFile;

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.txt');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.txt');

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn($attachment);

        $view = $strategy->render($model, 'test');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<a[^>]* href="http://some\.url/testing\.txt" [^>]*>'
            . '\s*testing\.txt\s*'
            . '</a>#i',
            $view->render()
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_source_is_not_a_stapler_attachment()
    {
        $strategy = new StaplerFile;

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn(null);

        $strategy->render($model, 'test');
    }

}
