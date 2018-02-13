<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Strategies\ListColumn\PaperclipFile;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\Paperclip\Attachment\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Mockery;

/**
 * Class PaperclipFileTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class PaperclipFileTest extends AbstractPostCommentSeededTestCase
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
    function it_renders_a_paperclip_attachment_as_a_link()
    {
        $strategy = new PaperclipFile;

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.txt');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.txt');
        $attachment->shouldReceive('size')->andReturn(123);

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
    function it_throws_an_exception_if_source_is_not_a_paperclip_attachment()
    {
        $strategy = new PaperclipFile;

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn(null);

        $strategy->render($model, 'test');
    }

}
