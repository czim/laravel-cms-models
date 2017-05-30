<?php
namespace Czim\CmsModels\Test\Strategies\ListColumn;

use Codesleeve\Stapler\Attachment;
use Codesleeve\Stapler\AttachmentConfig;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Strategies\ListColumn\StaplerImage;
use Czim\CmsModels\Test\AbstractPostCommentSeededTestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Mockery;

/**
 * Class StaplerImageTest
 *
 * @group strategies
 * @group strategies-listcolumn
 */
class StaplerImageTest extends AbstractPostCommentSeededTestCase
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
    function it_renders_a_stapler_attachment_as_a_thumbnail_using_the_smallest_style()
    {
        $strategy = new StaplerImage;

        $config = new AttachmentConfig('test', [
            'styles' => [
                'skip'  => '',
                'large' => '500x500',
                'small' => '32x18',
                'tiny'  => '16x16',
            ]
        ]);

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.png');
        $attachment->shouldReceive('url')->with('tiny')->once()->andReturn('http://some.url/testing_tiny.png');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.png');
        $attachment->shouldReceive('getConfig')->andReturn($config);
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
    function it_renders_a_stapler_attachment_as_a_thumbnail_using_a_configured_style()
    {
        $strategy = new StaplerImage;

        $strategy->setOptions([
            'stapler_style' => 'large',
        ]);

        $config = new AttachmentConfig('test', [
            'styles' => [
                'large' => '500x500',
                'tiny'  => '16x16',
            ]
        ]);

        /** @var Attachment|Mockery\Mock $attachment */
        $attachment = Mockery::mock(Attachment::class);
        $attachment->shouldReceive('originalFilename')->andReturn('testing.png');
        $attachment->shouldReceive('url')->with('large')->once()->andReturn('http://some.url/testing_large.png');
        $attachment->shouldReceive('url')->andReturn('http://some.url/testing.png');
        $attachment->shouldReceive('getConfig')->andReturn($config);
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
    function it_throws_an_exception_if_source_is_not_a_stapler_attachment()
    {
        $strategy = new StaplerImage;

        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getAttribute')->with('test')->andReturn(null);

        $strategy->render($model, 'test');
    }

}
