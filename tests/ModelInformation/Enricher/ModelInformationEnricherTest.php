<?php
namespace Czim\CmsModels\Test\ModelInformation\Enricher;

use Czim\CmsModels\Contracts\Support\Factories\FormStoreStrategyFactoryInterface;
use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationDataException;
use Czim\CmsModels\Exceptions\ModelInformationEnrichmentException;
use Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData;
use Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData;
use Czim\CmsModels\ModelInformation\Data\ModelAttributeData;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\ModelInformation\Enricher\ModelInformationEnricher;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\FormDisplayStrategy;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Support\Factories\FormStoreStrategyFactory;
use Czim\CmsModels\Support\Validation\ValidationRuleMerger;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\Test\TestCase;
use Exception;
use Illuminate\Support\Collection;
use Mockery;

/**
 * Class ModelInformationEnricherTest
 *
 * @group enrichment
 */
class ModelInformationEnricherTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->app->bind(FormStoreStrategyFactoryInterface::class, FormStoreStrategyFactory::class);
        $this->app->bind(ValidationRuleMergerInterface::class, ValidationRuleMerger::class);
    }

    /**
     * @test
     */
    function it_sets_and_returns_a_collection_of_model_information()
    {
        $enricher = new ModelInformationEnricher;

        static::assertInstanceOf(Collection::class, $enricher->getAllModelInformation());
        static::assertTrue($enricher->getAllModelInformation()->isEmpty());

        $info = new Collection([
            'test_a' => new ModelInformation,
            'test_b' => new ModelInformation,
        ]);

        static::assertSame($enricher, $enricher->setAllModelInformation($info));

        static::assertSame($info, $enricher->getAllModelInformation());
    }

    /**
     * @test
     */
    function it_enriches_a_single_set_of_model_information()
    {
        $enricher = new ModelInformationEnricher;

        $info = $this->getModelInformation();

        $enriched = $enricher->enrich($info);

        static::assertInstanceOf(ModelInformation::class, $enriched);

        static::assertEquals('position', $enriched->list->default_sort);
        static::assertTrue($enriched->list->orderable);
        static::assertEquals('title', $enriched->reference->source);

        static::assertEquals(['id', 'title', 'number'], array_keys($enriched->list->columns));
        static::assertEquals('desc', $enriched->list->columns['id']->sort_direction);
        static::assertEquals(['any'], array_keys($enriched->list->filters));

        static::assertEquals(['title', 'number', 'single', 'many'], array_keys($enriched->form->fields));
        static::assertEquals(FormDisplayStrategy::TEXT, $enriched->form->fields['title']->display_strategy);
        static::assertFalse($enriched->form->fields['number']->required);
        static::assertTrue($enriched->form->fields['single']->required);
        static::assertEquals(FormDisplayStrategy::RELATION_PLURAL_AUTOCOMPLETE, $enriched->form->fields['many']->display_strategy);

        static::assertCount(4, $enriched->form->validation->create);
        static::assertCount(4, $enriched->form->validation->update);
        static::assertCount(3, $enriched->show->fields);
        static::assertCount(4, $enriched->export->columns);
    }

    /**
     * @test
     */
    function it_enriches_a_collection_of_model_information()
    {
        $collection = new Collection([
            'test' => $this->getModelInformation(),
        ]);

        $enricher = new ModelInformationEnricher;

        $enrichedCollection = $enricher->enrichMany($collection);

        static::assertInstanceOf(Collection::class, $enrichedCollection);
        static::assertCount(1, $enrichedCollection);

        /** @var ModelInformation $enriched */
        $enriched = $enrichedCollection->first();

        static::assertInstanceOf(ModelInformation::class, $enriched);

        static::assertEquals('position', $enriched->list->default_sort);
        static::assertTrue($enriched->list->orderable);
        static::assertEquals('title', $enriched->reference->source);
        static::assertEquals(['id', 'title', 'number'], array_keys($enriched->list->columns));
    }

    /**
     * @test
     */
    function it_decorates_and_rethrows_enrichment_exceptions()
    {
        $enricher = new ModelInformationEnricher;

        $info = $this->getModelInformation();
        $info->list->columns = [
            'new' => new ModelListColumnData([
                'strategy' => 'testing',
            ]),
        ];

        try {
            $enricher->enrich($info);

            static::fail('Exception should have been thrown');

        } catch (Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertRegExp('#' . preg_quote(TestPost::class) . '#', $e->getMessage());
            static::assertEquals('list.columns', $e->getSection());
            static::assertEquals('new', $e->getKey());
        }
    }

    /**
     * @test
     */
    function it_decorates_and_rethrows_model_configuration_exceptions_as_enrichment_exceptions()
    {
        /** @var Mockery\Mock|ModelListColumnData $dataMock */
        $dataMock = Mockery::mock(ModelListColumnData::class);
        $dataMock->shouldReceive('merge')->andThrow(
            (new ModelConfigurationDataException('testing'))->setDotKey('test.dot.key')
        );

        $enricher = new ModelInformationEnricher;
        $info = $this->getModelInformation();

        $info->form->layout = [
            'title',
            [
                'type' => 'group',
                'test' => 'value',
            ]
        ];

        try {
            $enricher->enrich($info);

            static::fail('Exception should have been thrown');

        } catch (Exception $e) {

            /** @var ModelInformationEnrichmentException $e */
            static::assertInstanceOf(ModelInformationEnrichmentException::class, $e);
            static::assertRegExp('#' . preg_quote(TestPost::class) . '#', $e->getMessage());
            static::assertRegExp('#' . preg_quote(ModelFormFieldGroupData::class) . '#', $e->getMessage());
            static::assertRegExp('#\(test\)#', $e->getMessage());
        }
    }


    /**
     * @return ModelInformation
     */
    protected function getModelInformation()
    {
        $info = new ModelInformation([
            'model'          => TestPost::class,
            'original_model' => TestPost::class,
        ]);

        $info->list->orderable    = true;
        $info->list->order_column = 'position';
        $info->incrementing       = true;

        $info->attributes = [
            'id'       => new ModelAttributeData([
                'name'     => 'id',
                'cast'     => AttributeCast::INTEGER,
                'type'     => 'int',
                'unsigned' => true,
            ]),
            'title'    => new ModelAttributeData([
                'name'   => 'title',
                'cast'   => AttributeCast::STRING,
                'type'   => 'varchar',
                'length' => 200,
            ]),
            'position' => new ModelAttributeData([
                'name' => 'position',
            ]),
            'number'   => new ModelAttributeData([
                'name'     => 'number',
                'cast'     => AttributeCast::INTEGER,
                'type'     => 'mediumint',
                'unsigned' => false,
            ]),
            'test_id'  => new ModelAttributeData([
                'name'     => 'test_id',
                'cast'     => AttributeCast::INTEGER,
                'type'     => 'int',
                'unsigned' => true,
                'nullable' => false,
            ]),
        ];

        $info->relations = [
            'single' => new ModelRelationData([
                'name'         => 'single',
                'method'       => 'single',
                'type'         => RelationType::BELONGS_TO,
                'foreign_keys' => [
                    'test_id',
                ],
            ]),
            'many'   => new ModelRelationData([
                'name'   => 'many',
                'method' => 'many',
                'type'   => RelationType::HAS_MANY,
            ]),
        ];

        return $info;
    }

}
