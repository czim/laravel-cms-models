<?php
namespace Czim\CmsModels\Test\Support;

use Czim\CmsModels\Support\ModuleHelper;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModuleHelperTest
 *
 * @group support
 * @group support-helpers
 */
class ModuleHelperTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_model_slug_for_a_model_class()
    {
        $helper = new ModuleHelper;

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->modelSlug(TestPost::class)
        );
    }

    /**
     * @test
     */
    function it_returns_the_model_slug_for_a_model_instance()
    {
        $helper = new ModuleHelper;

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->modelSlug(new TestPost)
        );
    }

    /**
     * @test
     */
    function it_returns_the_module_key_for_a_model()
    {
        $helper = new ModuleHelper;

        static::assertEquals(
            'models.czim-cmsmodels-test-helpers-models-testpost',
            $helper->moduleKeyForModel(TestPost::class)
        );
    }

    /**
     * @test
     */
    function it_returns_the_model_information_key_for_a_model()
    {
        $helper = new ModuleHelper;

        static::assertEquals(
            'czim-cmsmodels-test-helpers-models-testpost',
            $helper->modelInformationKeyForModel(TestPost::class)
        );
    }

}
