<?php
namespace Czim\CmsModels\Test\Filters;

use Czim\CmsModels\Filters\ModelFilterData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;

class ModelFilterDataTest extends TestCase
{

    /**
     * @test
     */
    function it_sets_defaults_based_on_filters()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'list' => [
                'filters' => [
                    'title' => [
                        'target'   => 'title',
                        'strategy' => 'test',
                    ],
                    'active' => [
                        'target'   => 'active',
                        'strategy' => 'test',
                    ],
                ],
            ],
        ]);

        $data = new ModelFilterData($info, []);

        static::assertEquals(['title', 'active'], array_keys($data->getDefaults()));
    }

}
