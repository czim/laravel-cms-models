<?php
namespace Czim\CmsModels\Test;

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

        $app['config']->set('cms-models.analyzer.traits.listify', [
            \Czim\Listify\Listify::class,
        ]);

        $app['view']->addNamespace('cms-models', realpath(dirname(__DIR__) . '/resources/views'));
    }

}
