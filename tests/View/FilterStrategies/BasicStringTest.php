<?php
namespace Czim\CmsModels\Test\View\FilterStrategies;

use Czim\CmsModels\Contracts\Repositories\ModelInformationRepositoryInterface;
use Czim\CmsModels\Contracts\View\FilterStrategyInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Czim\CmsModels\View\FilterStrategies\BasicString;

class BasicStringTest extends AbstractFilterStrategyTestCase
{

    /**
     * @test
     */
    function it_filters_on_a_single_direct_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'description', 'best');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // Make sure we get no hits when we shouldn't
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'doesnotmatchanythingatall');
        static::assertEquals(0, $query->count());

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'description', 'e');
        static::assertEquals(3, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_a_single_translated_attribute()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'title', 'basic');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // Make sure we get all hits when we should
        $query = $this->getPostQuery();
        $strategy->apply($query, 'title', 'title');
        static::assertEquals(3, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_a_single_translated_attribute_for_the_current_locale()
    {
        app()->setLocale('nl');

        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'title', 'nederland');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // For translated attribute on related model
        $query = $this->getPostQuery();
        $strategy->apply($query, 'comments.title', 'nederland');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // Make sure we get no hits for a non-matching term
        $query = $this->getPostQuery();
        $strategy->apply($query, 'title', 'doesnotexistatall');
        static::assertEquals(0, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_a_single_translated_attribute_for_the_fallback_locale()
    {
        app()->setLocale('nl');

        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'title', 'some basic title');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // For translated attribute on related model
        $query = $this->getPostQuery();
        $strategy->apply($query, 'comments.title', 'Comment Title A');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);
    }


    /**
     * @test
     */
    function it_filters_on_an_attribute_on_a_belongs_to_related_model()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'author.name', 'tosti');

        static::assertEquals(1, $query->count());
        static::assertEquals(3, $query->first()['id']);

        // Make sure we get no hits on a nonexisting value
        $query = $this->getPostQuery();
        $strategy->apply($query, 'author.name', 'doesnotexistatall');
        static::assertEquals(0, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_an_attribute_on_a_has_many_related_model()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'comments.description', 'comment one');

        static::assertEquals(1, $query->count());
        static::assertEquals(1, $query->first()['id']);

        // Make sure we get no hits on a nonexisting value
        $query = $this->getPostQuery();
        $strategy->apply($query, 'comments.description', 'doesnotexistatall');
        static::assertEquals(0, $query->count());
    }

    /**
     * @test
     */
    function it_filters_on_an_attribute_on_a_translated_attribute_on_a_related_model()
    {
        $strategy = $this->makeFilterStrategy();
        $query = $this->getPostQuery();

        $strategy->apply($query, 'comments.title', 'title b');

        static::assertEquals(1, $query->count());
        static::assertEquals(2, $query->first()['id']);

        // Make sure we get no hits on a nonexisting value
        $query = $this->getPostQuery();
        $strategy->apply($query, 'comments.title', 'doesnotexistatall');
        static::assertEquals(0, $query->count());
    }


    /**
     * @test
     */
    function it_filters_on_all_string_fields_for_a_model_boolean_or_combined()
    {
        $strategy = $this->makeFilterStrategy();

        // This needs to interpret the model's properties, so it will
        // require mocking the model repository interface
        $this->bindMockModelRepositoryForPostModel();

        // Check for title
        $query = $this->getPostQuery();
        $strategy->apply($query, '*', 'title');
        static::assertEquals(3, $query->count());

        // Check for body
        $query = $this->getPostQuery();
        $strategy->apply($query, '*', 'pancake');
        static::assertEquals(2, $query->count());
        static::assertEquals([2, 3], $query->pluck('id')->toArray());

        // Check for description
        $query = $this->getPostQuery();
        $strategy->apply($query, '*', 'alternative');
        static::assertEquals(1, $query->count());
        static::assertEquals(2, $query->first()['id']);
    }

    /**
     * @test
     */
    function it_filters_on_multiple_comma_separated_different_target_attributes()
    {
        $strategy = $this->makeFilterStrategy();

        $query = $this->getPostQuery();
        $strategy->apply($query, 'description,title', 'title');
        static::assertEquals(3, $query->count());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,description', 'testing');
        static::assertEquals(2, $query->count());
        static::assertEquals([1, 2], $query->pluck('id')->toArray());

        $query = $this->getPostQuery();
        $strategy->apply($query, 'body,author.name,description', 'tosti');
        static::assertEquals(2, $query->count());
        static::assertEquals([2, 3], $query->pluck('id')->toArray());
    }


    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getPostQuery()
    {
        return TestPost::query();
    }

    /**
     * @return FilterStrategyInterface
     */
    protected function makeFilterStrategy()
    {
        return new BasicString();
    }

    /**
     * Binds mock repository with mocked information for the test post model.
     */
    protected function bindMockModelRepositoryForPostModel()
    {
        $this->app->bind(ModelInformationRepositoryInterface::class, function () {
            return $this->getMockModelRepository();
        });
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModelInformationRepositoryInterface
     */
    protected function getMockModelRepository()
    {
        $mock = $this->getMockBuilder(ModelInformationRepositoryInterface::class)->getMock();

        $mock->method('getByModel')
            ->with(static::isInstanceOf(TestPost::class))
            ->willReturn(
                new ModelInformation([
                    'attributes' => [
                        'title' => new ModelAttributeData([
                            'type'       => 'varchar',
                            'cast'       => 'string',
                            'translated' => true,
                        ]),
                        'body' => new ModelAttributeData([
                            'type'       => 'text',
                            'cast'       => 'string',
                            'translated' => true,
                        ]),
                        'description' => new ModelAttributeData([
                            'type'       => 'varchar',
                            'cast'       => 'string',
                        ]),
                    ],
                ])
            );

        return $mock;
    }
}
