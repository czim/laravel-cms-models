<?php
namespace Czim\CmsModels\Test\ModelInformation\Collector;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\ModelInformation\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Contracts\ModelInformation\Collector\ModelInformationFileReaderInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationEnricherInterface;
use Czim\CmsModels\Contracts\ModelInformation\ModelInformationInterpreterInterface;
use Czim\CmsModels\Contracts\Support\ModuleHelperInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\Exceptions\ModelInformationCollectionException;
use Czim\CmsModels\ModelInformation\Collector\ModelInformationCollector;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Exception;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelInformationCollectorTest
 *
 * @group collection
 */
class ModelInformationCollectorTest extends TestCase
{

    /**
     * @test
     */
    function it_collects_model_information()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
            TestComment::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')
            ->andReturn('models.test-post', 'models.test-comment');

        $modelAnalyzer->shouldReceive('analyze')
            ->andReturn(
                new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ]),
                new ModelInformation([
                    'model'          => TestComment::class,
                    'original_model' => TestComment::class,
                ])
            );

        $fileReader->shouldReceive('read')->andReturn(['single' => true]);

        $enricher->shouldReceive('enrichMany')
            ->andReturnUsing(function ($input) {
                return $input;
            });

        $interpreter->shouldReceive('interpret')
            ->andReturn(new ModelInformation([
                'single' => true,
            ]));

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        $collection = $collector->collect();

        // Assert
        static::assertInstanceOf(Collection::class, $collection);
        static::assertCount(2, $collection);
    }
    
    /**
     * @test
     */
    function it_throws_a_decorated_exception_if_the_analyzer_throws_an_exception()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')->andReturn('models.test-post');

        $modelAnalyzer->shouldReceive('analyze')->andThrow(new Exception('testing'));

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        try {
            $collector->collect();

            static::fail('Exception should have been thrown');

        } catch (ModelInformationCollectionException $e) {

            static::assertEquals(TestPost::class, $e->getModelClass());
        }
    }

    /**
     * @test
     */
    function it_throws_a_decorated_exception_if_the_interpreter_throws_an_exception()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')->andReturn('models.test-post');

        $fileReader->shouldReceive('read')->andReturn(['single' => true]);

        $modelAnalyzer->shouldReceive('analyze')
            ->andReturn(
                new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ])
            );

        $interpreter->shouldReceive('interpret')
            ->andThrow(
                (new ModelConfigurationDataException('testing'))->setDotKey('test.dot.key')
            );

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        try {
            $collector->collect();

            static::fail('Exception should have been thrown');

        } catch (ModelInformationCollectionException $e) {

            static::assertEquals(
                realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector/TestPost.php'),
                $e->getConfigurationFile()
            );
            static::assertRegExp('#\(test\.dot\.key\)#', $e->getMessage());
        }
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelInformationCollectionException
     * @expectedExceptionMessageRegExp #TestPost.php#
     */
    function it_throws_an_exception_if_model_configuration_read_from_file_is_unexpected()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')->andReturn('models.test-post');

        $modelAnalyzer->shouldReceive('analyze')
            ->andReturn(
                new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ])
            );

        $fileReader->shouldReceive('read')->andReturn('not an array');

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        $collector->collect();
    }

    /**
     * @test
     */
    function it_silently_skips_model_configuration_files_if_directory_configured_does_not_exist()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            '/../apaththatdoesnotexist'
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')
            ->andReturn('models.test-post');

        $modelAnalyzer->shouldReceive('analyze')
            ->andReturn(
                new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ])
            );

        $enricher->shouldReceive('enrichMany')
            ->andReturnUsing(function ($input) {
                return $input;
            });

        $interpreter->shouldReceive('interpret')
            ->andReturn(new ModelInformation([
                'single' => true,
            ]));

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        $collector->collect();
    }

    /**
     * @test
     */
    function it_logs_a_warning_if_a_cms_configured_model_is_unknown_by_key()
    {
        // Configure
        $this->app['config']->set('cms-models.models', [
            TestPost::class,
        ]);

        $this->app['config']->set(
            'cms-models.collector.source.dir',
            realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Collector')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-dir',
            realpath(__DIR__ . '/../../Helpers/Models')
        );
        $this->app['config']->set(
            'cms-models.collector.source.models-namespace',
            'Czim\\CmsModels\\Test\\Helpers\\Models'
        );

        // Setup
        $moduleHelper  = $this->getMockModuleHelper();
        $modelAnalyzer = $this->getMockModelAnalyzer();
        $fileReader    = $this->getMockModelInformationFileReader();
        $enricher      = $this->getMockModelInformationEnricher();
        $interpreter   = $this->getMockModelInformationInterpreter();

        $moduleHelper->shouldReceive('modelInformationKeyForModel')
            ->andReturn('models.test-post', 'models.key-that-does-not-exist');

        $fileReader->shouldReceive('read')->andReturn(['single' => true]);

        $modelAnalyzer->shouldReceive('analyze')
            ->andReturn(
                new ModelInformation([
                    'model'          => TestPost::class,
                    'original_model' => TestPost::class,
                ])
            );

        $enricher->shouldReceive('enrichMany')
            ->andReturnUsing(function ($input) {
                return $input;
            });

        $interpreter->shouldReceive('interpret')
            ->andReturn(new ModelInformation([
                'single' => true,
            ]));

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('log')->once();

        $this->app->instance(Component::CORE, $mockCore);

        // Test
        $collector = new ModelInformationCollector(
            $moduleHelper,
            $modelAnalyzer,
            $fileReader,
            $enricher,
            $interpreter,
            app(\Illuminate\Filesystem\Filesystem::class)
        );

        $collector->collect();
    }

    /**
     * @return ModuleHelperInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModuleHelper()
    {
        return Mockery::mock(ModuleHelperInterface::class);
    }

    /**
     * @return ModelAnalyzerInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelAnalyzer()
    {
        return Mockery::mock(ModelAnalyzerInterface::class);
    }

    /**
     * @return ModelInformationFileReaderInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelInformationFileReader()
    {
        return Mockery::mock(ModelInformationFileReaderInterface::class);
    }

    /**
     * @return ModelInformationEnricherInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelInformationEnricher()
    {
        return Mockery::mock(ModelInformationEnricherInterface::class);
    }

    /**
     * @return ModelInformationInterpreterInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModelInformationInterpreter()
    {
        return Mockery::mock(ModelInformationInterpreterInterface::class);
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
