<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Resolvers;

use Czim\CmsModels\ModelInformation\Analyzer\Resolvers\RelationValidationResolver;
use Czim\CmsModels\ModelInformation\Data\Form\ModelFormFieldData;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\TestCase;

class RelationValidationResolverTest extends TestCase
{

    /**
     * @test
     */
    function it_determines_validation_rules_for_singular_relations()
    {
        $resolver = new RelationValidationResolver;

        $data = new ModelRelationData([
            'type' => RelationType::BELONGS_TO,
        ]);

        $field = new ModelFormFieldData([
            'required' => true,
        ]);

        static::assertEquals(
            ['required'],
            $resolver->determineValidationRules($data, $field)
        );
    }

}
