<?php
namespace Czim\CmsModels\Test;

use App\Console\Kernel;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('translatable.locales', [ 'en', 'nl' ]);
        $app['config']->set('translatable.use_fallback', true);
        $app['config']->set('translatable.fallback_locale', 'en');

        $app['config']->set('cms-models', include(realpath(dirname(__DIR__) . '/config/cms-models.php')));
        $app['config']->set('cms-models.analyzer.database.class', null);

        $app['view']->addNamespace('cms-models', realpath(dirname(__DIR__) . '/resources/views'));
    }

    /**
     * @return string
     */
    protected function getModelsCachePath()
    {
        return realpath(__DIR__ .'/../vendor/orchestra/testbench/fixture/bootstrap/cache') . '/cms_model_information.php';
    }

    /**
     * Deletes the menu cache file if it exists.
     */
    protected function deleteModelsCacheFile()
    {
        if (file_exists($this->getModelsCachePath())) {
            unlink($this->getModelsCachePath());
        }
    }

    /**
     * Returns most recent artisan command output.
     *
     * @return string
     */
    protected function getArtisanOutput()
    {
        return $this->getConsoleKernel()->output();
    }

    /**
     * @return ConsoleKernelContract|Kernel
     */
    protected function getConsoleKernel()
    {
        return $this->app[ConsoleKernelContract::class];
    }

}
