<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form\Layout;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\AbstractModelFormLayoutNodeData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldLabelData;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldsetData;
use Czim\CmsModels\Support\Enums\LayoutNodeType;
use Czim\CmsModels\Test\TestCase;
use Mockery;

abstract class AbstractModelFormLayoutNodeDataTestCase extends TestCase
{

    /**
     * @return AbstractModelFormLayoutNodeData
     */
    abstract protected function makeDataObject();

    /**
     * @test
     */
    function it_returns_type()
    {
        $data = $this->makeDataObject();

        $data->type = 'test';

        static::assertEquals('test', $data->type());
    }

    /**
     * @test
     */
    function it_returns_untranslated_label()
    {
        $data = $this->makeDataObject();

        $data->label = 'Testing';

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Testing', $data->display());
    }

    /**
     * @test
     */
    function it_returns_translated_label()
    {
        $data = $this->makeDataObject();

        $data->label_translated = 'testing.translation';
        $data->label            = 'Testing';

        $this->app->setLocale('en');
        $this->app['translator']->addLines(['testing.translation' => 'Exists'], 'en', '*');

        $mockCore = $this->getMockCore();
        $mockCore->shouldReceive('config')->with('translation.prefix', Mockery::any())->andReturn('cms::');
        $this->app->instance(Component::CORE, $mockCore);

        static::assertEquals('Exists', $data->display());
    }

    /**
     * @test
     */
    function it_returns_whether_it_is_marked_required()
    {
        $data = $this->makeDataObject();

        static::assertFalse($data->required());

        $data->required = true;

        static::assertTrue($data->required());
    }

    /**
     * @test
     */
    function it_returns_children()
    {
        $data = $this->makeDataObject();

        $data->children = ['test'];

        static::assertEquals(['test'], $data->children());
    }

    /**
     * @test
     */
    function it_returns_null_for_unset_children_attribute()
    {
        $data = $this->makeDataObject();

        $data->children = null;

        static::assertNull($data->children);
    }

    /**
     * @test
     */
    function it_returns_descendant_field_keys()
    {
        $data = $this->makeDataObject();

        $data->children = [
            [
                'label'    => 'tab-a',
                'type'     => LayoutNodeType::GROUP,
                'children' => [
                    'field_c',
                    [
                        'type'     => LayoutNodeType::GROUP,
                        'children' => [
                            'field_d',
                        ],
                    ],
                ],
            ],
            'field_a',
            'field_b',
        ];

        $keys = $data->descendantFieldKeys();
        sort($keys);

        static::assertEquals(['field_a', 'field_b', 'field_c', 'field_d'], $keys);
    }

    /**
     * @test
     */
    function it_decorates_children()
    {
        $data = $this->makeDataObject();

        $data->children = [
            'group'    => [
                'type' => LayoutNodeType::GROUP,
            ],
            'fieldset' => [
                'type' => LayoutNodeType::FIELDSET,
            ],
            'label'    => [
                'type' => LayoutNodeType::LABEL,
            ],
        ];

        $children = $data->children;

        static::assertInstanceOf(ModelFormFieldGroupData::class, $children['group']);
        static::assertInstanceOf(ModelFormFieldsetData::class, $children['fieldset']);
        static::assertInstanceOf(ModelFormFieldLabelData::class, $children['label']);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    function it_throws_an_exception_if_it_cannot_decorate_a_child()
    {
        $data = $this->makeDataObject();

        $data->children = [
            'tab' => [
                'type' => 'unknown',
            ],
        ];

        $data->children;
    }

    /**
     * @test
     */
    function it_throws_a_decorated_exception_if_a_child_throws_a_model_configuration_exception()
    {
        $data = $this->makeDataObject();

        $data->children = [
            'a' => [
                'type'    => LayoutNodeType::FIELDSET,
                'unknown' => true,
            ],
        ];

        try {
            $data->children;

            static::fail("Should have thrown exception");

        } catch (ModelConfigurationDataException $e) {

            static::assertEquals('children.a.unknown', $e->getDotKey());
        }
    }

    /**
     * @return CoreInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockCore()
    {
        return Mockery::mock(CoreInterface::class);
    }

}
