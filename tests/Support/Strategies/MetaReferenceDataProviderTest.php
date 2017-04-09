<?php
namespace Czim\CmsModels\Test\Support\Strategies;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Support\Data\Strategies\ModelMetaReference;
use Czim\CmsModels\Support\Strategies\MetaReferenceDataProvider;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestRelation;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\Helpers\Models\TestSeo;
use Czim\CmsModels\Test\TestCase;
use Mockery;

/**
 * Class MetaReferenceDataProviderTest
 *
 * @group support
 * @group support-strategies
 */
class MetaReferenceDataProviderTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      Form Field: Reference Data
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_model_reference_data_for_model_information_with_a_form_field_key_reference_with_specified_target_model()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'form'           => [
                'fields' => [
                    'testing' => [
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getForInformationByType($postInfo, 'form.field', 'testing', TestComment::class);

        static::assertInstanceOf(ModelMetaReference::class, $data);

        static::assertEquals(TestComment::class, $data->model());
        static::assertEquals('custom', $data->strategy());
        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     */
    function it_returns_model_reference_data_for_model_information_with_a_form_field_key_reference_with_specified_target_model_with_configured_models_data_key()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'form'           => [
                'fields' => [
                    'testing' => [
                        'options' => [
                            'models' => [
                                TestComment::class => [
                                    'strategy' => 'alternative',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getForInformationByType($postInfo, 'form.field', 'testing', TestComment::class);

        static::assertInstanceOf(ModelMetaReference::class, $data);

        static::assertEquals(TestComment::class, $data->model());
        static::assertEquals('alternative', $data->strategy());
        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     */
    function it_returns_model_reference_data_for_model_information_with_a_form_field_key_reference_resolving_the_target_model_from_a_relation()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
            'form'           => [
                'fields' => [
                    'comments' => [
                        'source' => 'comments',
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getForInformationByType($postInfo, 'form.field', 'comments');

        static::assertInstanceOf(ModelMetaReference::class, $data);

        static::assertEquals(TestComment::class, $data->model());
        static::assertEquals('custom', $data->strategy());
        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessageRegExp #testIsNotARelationMethod is not an Eloquent relation#i
     */
    function it_throws_an_exception_when_trying_to_return_model_reference_data_for_form_field_key_if_it_resolves_to_a_non_eloquent_method()
    {
        $testInfo = new ModelInformation([
            'model'          => TestRelation::class,
            'original_model' => TestRelation::class,
            'form'           => [
                'fields' => [
                    'testIsNotARelationMethod' => [
                        'source' => 'testIsNotARelationMethod',
                    ],
                ],
            ],
        ]);

        $provider = new MetaReferenceDataProvider;

        $provider->getForInformationByType($testInfo, 'form.field', 'testIsNotARelationMethod');
    }

    /**
     * @test
     */
    function it_returns_model_reference_data_for_a_form_field_key_reference_with_specified_target_model()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'form' => [
                'fields' => [
                    'testing' => [
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($postInfo);
        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getForModelClassByType(TestPost::class, 'form.field', 'testing', TestComment::class);

        static::assertInstanceOf(ModelMetaReference::class, $data);

        static::assertEquals(TestComment::class, $data->model());
        static::assertEquals('custom', $data->strategy());
        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     * @uses \Czim\CmsModels\ModelInformation\Data\ModelInformation
     */
    function it_returns_model_reference_data_for_a_form_field_key_reference()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'form' => [
                'fields' => [
                    'testing' => [
                        'options' => [
                            'model' => TestComment::class,
                        ],
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($postInfo);
        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getForModelClassByType(TestPost::class, 'form.field', 'testing');

        static::assertInstanceOf(ModelMetaReference::class, $data);
        static::assertEquals('custom', $data->strategy());
        static::assertEquals('testing', $data->source());
    }

    /**
     * @test
     */
    function it_returns_false_for_model_reference_data_if_it_could_not_be_resolved()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        $info = new ModelInformation();

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestPost::class)->andReturn($info);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getForModelClassByType(TestPost::class, 'form.field', 'testing'));
    }


    // ------------------------------------------------------------------------------
    //      Form Field: Reference Model Classes
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_nested_model_classes_for_a_form_field_key_reference()
    {
        $modelInfoRepoMock = $this->getMockLocaleRepository();

        // Model with the relation
        $postInfo = new ModelInformation([
            'form' => [
                'fields' => [
                    'testing' => [
                        'options' => [
                            'models' => [
                                TestComment::class,
                                TestSeo::class,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // Model related to, for which a reference would be required
        $commentInfo = new ModelInformation([
            'reference' => [
                'strategy' => 'custom',
                'source'   => 'testing',
            ],
        ]);

        $modelInfoRepoMock->shouldReceive('getByModelClass')->with(TestComment::class)->andReturn($commentInfo);

        $this->app->instance(ModelInformationRepositoryInterface::class, $modelInfoRepoMock);

        $provider = new MetaReferenceDataProvider;

        $data = $provider->getNestedModelClassesByType($postInfo, 'form.field', 'testing');

        static::assertInternalType('array', $data);
        static::assertEquals([TestComment::class, TestSeo::class], $data);
    }

    /**
     * @test
     */
    function it_returns_false_for_nested_model_classes_for_an_unknown_form_field_key()
    {
        $postInfo = new ModelInformation([
            'form' => [
                'fields' => [
                ],
            ],
        ]);

        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getNestedModelClassesByType($postInfo, 'form.field', 'does-not-exist'));
    }

    /**
     * @test
     */
    function it_returns_false_for_nested_model_classes_for_form_field_key_when_no_morphable_models_were_listed()
    {
        $postInfo = new ModelInformation([
            'form' => [
                'fields' => [
                    'testing' => [
                        'options' => [
                            'models' => [],
                        ],
                    ],
                ],
            ],
        ]);

        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getNestedModelClassesByType($postInfo, 'form.field', 'testing'));
    }


    // ------------------------------------------------------------------------------
    //      Unknown Type
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_false_for_model_reference_data_by_information_if_it_could_not_be_resolved()
    {
        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getForInformationByType(new ModelInformation, 'form.field', 'testing'));
    }

    /**
     * @test
     */
    function it_returns_false_when_looking_up_by_information_for_an_unknown_type()
    {
        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getForInformationByType(new ModelInformation, 'unknown.type', 'testing'));
    }

    /**
     * @test
     */
    function it_returns_false_when_looking_up_nested_model_classes_for_an_unknown_type()
    {
        $provider = new MetaReferenceDataProvider;

        static::assertFalse($provider->getNestedModelClassesByType(new ModelInformation, 'unknown.type', 'testing'));
    }


    /**
     * @return ModelInformationRepositoryInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function getMockLocaleRepository()
    {
        return Mockery::mock(ModelInformationRepositoryInterface::class);
    }

}
