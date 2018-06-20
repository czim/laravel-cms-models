<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\Strategies\Form\Store\PaperclipStrategy;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Mockery;

/**
 * Class PaperclipStrategyTest
 *
 * @group strategies
 * @group strategies-form-store
 */
class PaperclipStrategyTest extends AbstractFormStoreStrategyTest
{

    /**
     * @test
     */
    function it_retrieves_a_value_from_a_model()
    {
        $model = new TestPost;
        $model->test = 'test';

        $data = new ModelFormFieldData;

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        static::assertEquals('test', $strategy->retrieve($model, 'test'));
    }

    /**
     * @test
     */
    function it_stores_a_value_on_a_model_if_keep_is_not_flagged()
    {
        $this->app->instance(Component::MODULES, $this->getMockModules());

        $model = new TestPost;
        $model->test = false;

        $data = new ModelFormFieldData;

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'test', [
            'keep'   => false,
            'upload' => 'testing',
        ]);

        static::assertEquals('testing', $model->test);
    }

    /**
     * @test
     */
    function it_does_not_store_a_value_on_a_model_if_keep_is_flagged()
    {
        $this->app->instance(Component::MODULES, $this->getMockModules());

        $model = new TestPost;
        $model->test = false;

        $data = new ModelFormFieldData;

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        $strategy->store($model, 'test', [
            'keep'   => true,
            'upload' => 'testing',
        ]);

        static::assertFalse($model->test);
    }

    /**
     * @return ModuleManagerInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockModules()
    {
        /** @var ModuleManagerInterface|Mockery\MockInterface|Mockery\Mock $manager */
        $manager = Mockery::mock(ModuleManagerInterface::class);

        $manager->shouldReceive('get')->with('file-uploader')->andReturn(null);

        return $manager;
    }

}
