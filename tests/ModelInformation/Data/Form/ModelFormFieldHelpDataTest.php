<?php
namespace Czim\CmsModels\Test\ModelInformation\Data\Form;

use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldHelpData;
use Czim\CmsModels\Test\TestCase;

/**
 * Class ModelFormFieldHelpDataTest
 *
 * @group modelinformation-data
 */
class ModelFormFieldHelpDataTest extends TestCase
{
    
    /**
     * @test
     */
    function it_merges_with_another_dataobject()
    {
        $data = new ModelFormFieldHelpData;

        $data->label = ['text' => 'testing'];
        $data->field = ['text' => 'testing field'];

        $with = new ModelFormFieldHelpData;

        $with->label = ['icon' => 'new icon'];

        $data->merge($with);

        static::assertEquals('testing field', $data->field->text);
        static::assertEquals('testing', $data->label->text);
        static::assertEquals('new icon', $data->label->icon);
    }
    
}
