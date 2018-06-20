<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
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
     * @test
     */
    function it_returns_validation_rules()
    {
        $data = new ModelFormFieldData;

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'attachment' => [],
                ],
            ],
        ]);

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        /** @var array|ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, true);

        static::assertCount(3, $rules);
        static::assertArrayHasKey('keep', $rules);
        static::assertArrayHasKey('upload', $rules);
        static::assertArrayHasKey('upload_id', $rules);
        static::assertEquals(['boolean', 'nullable'], $rules['keep']);
        static::assertEquals(['file', 'nullable'], $rules['upload']);
        static::assertEquals(['integer', 'nullable'], $rules['upload_id']);
    }

    /**
     * @test
     */
    function it_returns_validation_rules_with_custom_configured_additions()
    {
        $data = new ModelFormFieldData([
            'key'     => 'attachment',
            'options' => [
                'validation' => [
                    'dimensions:min_width=100,min_height=200',
                    'max:40000',
                ],
            ],
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'attachment' => [],
                ],
            ],
        ]);

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        /** @var array|ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, true);

        static::assertCount(3, $rules);
        static::assertArrayHasKey('keep', $rules);
        static::assertArrayHasKey('upload', $rules);
        static::assertArrayHasKey('upload_id', $rules);
        static::assertEquals(['boolean', 'nullable'], $rules['keep']);
        static::assertEquals(['dimensions:min_width=100,min_height=200', 'max:40000', 'file', 'nullable'], $rules['upload']);
        static::assertEquals(['integer', 'nullable'], $rules['upload_id']);
    }

    /**
     * @test
     */
    function it_returns_validation_rules_for_required_field()
    {
        $data = new ModelFormFieldData([
            'key'      => 'attachment',
            'required' => true,
        ]);

        $info = new ModelInformation([
            'form' => [
                'fields' => [
                    'attachment' => [],
                ],
            ],
        ]);

        $strategy = new PaperclipStrategy;
        $strategy->setFormFieldData($data);

        /** @var array|ValidationRuleDataInterface[] $rules */
        $rules = $strategy->validationRules($info, true);

        static::assertCount(3, $rules);
        static::assertArrayHasKey('keep', $rules);
        static::assertArrayHasKey('upload', $rules);
        static::assertArrayHasKey('upload_id', $rules);
        static::assertEquals(['boolean', 'required_without_all:<field>.upload,<field>.upload_id'], $rules['keep']);
        static::assertEquals(['file', 'required_without_all:<field>.upload_id,<field>.keep'], $rules['upload']);
        static::assertEquals(['integer', 'required_without_all:<field>.upload,<field>.keep'], $rules['upload_id']);
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
