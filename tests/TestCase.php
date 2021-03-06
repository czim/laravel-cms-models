<?php
namespace Czim\CmsModels\Test;

use App\Console\Kernel;
use Czim\CmsModels\Test\Helpers\Traits\InteractsWithPages;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Foundation\Application;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use InteractsWithPages;

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @var Repository $config */
        $config = $app['config'];

        $config->set('translatable.locales', [ 'en', 'nl' ]);
        $config->set('translatable.use_fallback', true);
        $config->set('translatable.fallback_locale', 'en');

        $config->set('cms-models', include(realpath(dirname(__DIR__) . '/config/cms-models.php')));
        $config->set('cms-models.analyzer.database.class', null);

        $config->set('cms-models.defaults.default-listing-action-edit', false);
        $config->set('cms-models.defaults.default-listing-action-show', false);

        $app['view']->addNamespace('cms-models', realpath(dirname(__DIR__) . '/resources/views'));

        $this->setUpPaperclip($app);
    }

    /**
     * @return string
     */
    protected function getModelsCachePath()
    {
        return realpath(__DIR__ .'/../vendor/orchestra/testbench-core/laravel/bootstrap/cache') . '/cms_model_information.php';
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

    /**
     * @param string      $selector
     * @param string|null $message
     */
    protected function assertHtmlElementInResponse($selector, $message = null)
    {
        static::assertGreaterThanOrEqual(
            1,
            $this->crawler()->filter($selector)->count(),
            ($message ?: "HTML element '{$selector}' not found in response.")
            . PHP_EOL . $this->crawler()->html()
        );
    }

    /**
     * @param string      $selector
     * @param string|null $message
     */
    protected function assertNotHtmlElementInResponse($selector, $message = null)
    {
        static::assertCount(
            0,
            $this->crawler()->filter($selector),
            ($message ?: "HTML element '{$selector}' found in response.")
            . PHP_EOL . $this->crawler()->html()
        );
    }

    /**
     * @param string      $href
     * @param string|null $text
     * @param string|null $message
     */
    protected function assertResponseHasLink($href, $text = null, $message = null)
    {
        $selector = 'a[href="' . $href .'"]';

        $filtered = $this->crawler()->filter($selector);

        static::assertGreaterThanOrEqual(
            1,
            $filtered->count(),
            ($message ?: "No link with href '{$href}' found in response.")
            . PHP_EOL . $this->crawler()->html()
        );

        // Check link text but only if it is set
        if (null === $text) {
            return;
        }

        foreach ($filtered as $match) {
            if ($match->textContent == $text) {
                return;
            }
        }

        static::fail($message ?: "Link found for '{$href}', but not matching text '{$text}'");
    }

    /**
     * @param string      $href
     * @param string|null $message
     */
    protected function assertNotResponseHasLink($href, $message = null)
    {
        $selector = 'a[href="' . $href .'"]';

        static::assertCount(
            0,
            $this->crawler()->filter($selector),
            ($message ?: "Link with href '{$href}' found in response.")
            . PHP_EOL . $this->crawler()->html()
        );
    }

    /**
     * Appends response HTML to message string.
     *
     * @param string $message
     * @return string
     */
    protected function appendResponseHtml($message)
    {
        return $message . PHP_EOL . $this->crawler()->html();
    }

    /**
     * Sets up paperclip attachment basics.
     *
     * @param Application $app
     * @return CmsBootTestCase
     */
    protected function setUpPaperclip($app)
    {
        $app->register(\Czim\Paperclip\Providers\PaperclipServiceProvider::class);

        /** @var Repository $config */
        $config = $app['config'];

        $config->set('filesystems.disks.paperclip', [
            'driver' => 'local',
            'root'   => public_path('system'),
        ]);

        return $this;
    }

}
