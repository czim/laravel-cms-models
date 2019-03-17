<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Strategies\ListColumn\PaperclipImage;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Czim\Paperclip\Attachment\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Mockery;

/**
 * Class PaperclipImageTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class PaperclipImageTest extends AbstractPostCommentSeededTestCase
{

    public function setUp(): void
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
    function it_renders_a_paperclip_attachment_as_a_thumbnail_using_the_smallest_style()
    {
        $strategy = new PaperclipImage;

        $config = [
            'variants' => [
                'large' => ['resize' => ['dimensions' => '500x500']],
                'small' => ['resize' => ['dimensions' => '32x18']],
                'tiny'  => ['resize' => ['dimensions' => '16x16']],
            ]
        ];

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.png');
        $attachment->shouldReceive('url')->with('tiny')->once()->andReturn('http://some.url/testing_tiny.png');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.png');
        $attachment->shouldReceive('getNormalizedConfig')->andReturn($config);
        $attachment->shouldReceive('size')->andReturn(1234);

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn($attachment);

        $view = $strategy->render($model, 'test');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<img[^>]* src="http://some.url/testing_tiny.png" [^>]*>#i',
            $view->render()
        );
    }

    /**
     * @test
     */
    function it_renders_a_paperclip_attachment_as_a_thumbnail_using_a_configured_style()
    {
        $strategy = new PaperclipImage;

        $strategy->setOptions([
            'variant' => 'large',
        ]);

        $config = [
            'variants' => [
                'large' => ['resize' => ['dimensions' => '500x500']],
                'tiny'  => ['resize' => ['dimensions' => '16x16']],
            ]
        ];

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.png');
        $attachment->shouldReceive('url')->with('large')->once()->andReturn('http://some.url/testing_large.png');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.png');
        $attachment->shouldReceive('getNormalizedConfig')->andReturn($config);
        $attachment->shouldReceive('size')->andReturn(1234);

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn($attachment);

        $view = $strategy->render($model, 'test');

        static::assertInstanceOf(View::class, $view);
        static::assertRegExp(
            '#<img[^>]* src="http://some.url/testing_large.png" [^>]*>#i',
            $view->render()
        );
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_source_is_not_a_paperclip_attachment()
    {
        $strategy = new PaperclipImage;

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn(null);

        $strategy->render($model, 'test');
    }

}
