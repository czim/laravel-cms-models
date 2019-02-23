<?php
namespace Czim\CmsModels\Test\Console;

use Czim\CmsModels\Console\Commands\ShowModelInformation;
use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestAuthor;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Collection;
use Mockery;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class ShowModelInformationTest
 *
 * @group console
 */
class ShowModelInformationTest extends ConsoleTestCase
{

    /**
     * @var Collection
     */
    protected $models;

    /**
     * @var bool
     */
    public $mockConsoleOutput = false;


    public function setUp()
    {
        parent::setUp();

        $this->getConsoleKernel()->registerCommand(new ShowModelInformation);

        $this->models = new Collection([
            'models.app-models-testauthor' => new ModelInformation([
                'model'               => TestAuthor::class,
                'original_model'      => TestAuthor::class,
                'verbose_name'        => 'Author',
                'verbose_name_plural' => 'Authors',
            ]),
            'models.app-models-testpost' => new ModelInformation([
                'model'               => TestPost::class,
                'original_model'      => TestPost::class,
                'verbose_name'        => 'Post',
                'verbose_name_plural' => 'Posts',
                'translated'          => true,
                'list' => [
                    'columns' => [
                        'test' => [
                            'strategy' => 'test',
                            'source'   => 'test',
                        ],
                        'another' => [
                            'strategy' => 'test',
                            'source'   => 'another',
                        ]
                    ],
                ],
            ]),
        ]);
    }

    /**
     * @test
     */
    function it_shows_keys_for_all_models()
    {
        $this->setUpModels();

        $this->artisan('cms:models:show', ['--keys' => true]);

        $output = $this->getArtisanOutput();

        static::assertRegExp('#model keys:#i', $output);
        static::assertRegExp('#\s+models\.app-models-testauthor\s+#im', $output);
        static::assertRegExp('#\s+models\.app-models-testpost\s+#im', $output);
    }

    /**
     * @test
     */
    function it_shows_information_for_all_models()
    {
        $this->setUpModels();

        $dumper = $this->getMockDumper();
        $this->app->instance(VarDumper::class, $dumper);

        $dumper->shouldReceive('dump')->once()->with(
            $this->models->get('models.app-models-testauthor')->toArray()
        );
        $dumper->shouldReceive('dump')->once()->with(
            $this->models->get('models.app-models-testpost')->toArray()
        );

        $this->artisan('cms:models:show');
    }

    /**
     * @test
     */
    function it_shows_information_for_a_model_by_module_key()
    {
        $this->setUpModels();

        $dumper = $this->getMockDumper();
        $this->app->instance(VarDumper::class, $dumper);

        $dumper->shouldReceive('dump')->once()->with(
            $this->models->get('models.app-models-testpost')->toArray()
        );

        $dumper->shouldReceive('dump')->never()->with(
            $this->models->get('models.app-models-testauthor')->toArray()
        );

        $this->artisan('cms:models:show', ['model' => 'models.app-models-testpost']);
    }

    /**
     * @test
     */
    function it_shows_information_for_a_model_by_model_class()
    {
        $this->setUpModels();

        $dumper = $this->getMockDumper();
        $this->app->instance(VarDumper::class, $dumper);

        $dumper->shouldReceive('dump')->once()->with(
            $this->models->get('models.app-models-testauthor')->toArray()
        );

        $dumper->shouldReceive('dump')->never()->with(
            $this->models->get('models.app-models-testpost')->toArray()
        );

        $this->artisan('cms:models:show', ['model' => TestAuthor::class]);
    }
    
    /**
     * @test
     */
    function it_shows_an_error_if_it_cannot_find_model_by_key_or_class()
    {
        $this->setUpModels();

        $this->artisan('cms:models:show', ['model' => 'models.does-not-exist']);

        static::assertRegExp('#unable to find (.*) ["\']?models\.does-not-exist["\']?#i', $this->getArtisanOutput());

        $this->artisan('cms:models:show', ['model' => TestComment::class]);

        static::assertRegExp('#["\']?' . preg_quote(TestComment::class) . '["\']?#i', $this->getArtisanOutput());
    }

    /**
     * @test
     */
    function it_plucks_data_for_a_specific_dot_notation_key()
    {
        $this->setUpModels();

        $dumper = $this->getMockDumper();
        $this->app->instance(VarDumper::class, $dumper);

        $dumper->shouldReceive('dump')->once()->with(
            array_get($this->models->get('models.app-models-testpost')->toArray(), 'list.columns')
        );

        $this->artisan('cms:models:show', ['model' => 'models.app-models-testpost', '--pluck' => 'list.columns']);
    }

    /**
     * @test
     */
    function it_plucks_only_keys_for_data_for_a_specific_dot_notation_key()
    {
        $this->setUpModels();

        $dumper = $this->getMockDumper();
        $this->app->instance(VarDumper::class, $dumper);

        $dumper->shouldReceive('dump')->once()->with(
            array_keys(array_get($this->models->get('models.app-models-testpost')->toArray(), 'list.columns'))
        );

        $this->artisan('cms:models:show', [
            'model'   => 'models.app-models-testpost',
            '--pluck' => 'list.columns',
            '--keys'  => true,
        ]);
    }

    /**
     * @test
     */
    function it_warns_when_there_is_nothing_to_pluck_for_a_given_key()
    {
        $this->setUpModels();

        $this->artisan('cms:models:show', ['model' => 'models.app-models-testpost', '--pluck' => 'does.not.exist']);

        static::assertRegExp('#nothing to pluck (.*) ["\']?does\.not\.exist["\']?#i', $this->getArtisanOutput());
    }


    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

    /**
     * @return VarDumper|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockDumper()
    {
        return Mockery::mock(VarDumper::class);
    }

    protected function setUpModels()
    {
        $this->models = $this->models ?: new Collection;

        $mock = $this->getMockRepository();

        $mock->shouldReceive('getAll')->andReturn($this->models);
        $mock->shouldReceive('getByKey')
            ->andReturnUsing(function ($key) { return $this->models->get($key); });
        $mock->shouldReceive('getByModelClass')
            ->andReturnUsing(function ($class) { return $this->models->where('model', $class)->first(); });

        $this->app->instance(ModelInformationRepositoryInterface::class, $mock);
    }

}
