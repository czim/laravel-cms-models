<?php
namespace Czim\CmsModels\Test\Integration\Analyzer\Processor;

use Czim\CmsModels\ModelInformation\Analyzer\Database\SqliteDatabaseAnalyzer;
use Czim\CmsModels\ModelInformation\Analyzer\Processor\ModelAnalyzer;
use Czim\CmsModels\Contracts\ModelInformation\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\RelationType;
use Czim\CmsModels\Test\Helpers\Models\TestPost;

class SqliteModelAnalysisTranslatedTest extends AbstractAnalyzerTestCase
{

    /**
     * @test
     */
    function it_analyzes_a_translated_model()
    {
        $analyzer = new ModelAnalyzer();

        $info = $analyzer->analyze(TestPost::class);

        static::assertInstanceOf(ModelInformation::class, $info);

        static::assertEquals(TestPost::class, $info['model']);
        static::assertEquals('test post', $info['verbose_name']);
        static::assertEquals('test posts', $info['verbose_name_plural']);

        static::assertTrue($info['incrementing']);
        static::assertTrue($info['timestamps']);
        static::assertEquals('created_at', $info['timestamp_created']);
        static::assertEquals('updated_at', $info['timestamp_updated']);

        static::assertTrue($info['translated']);
        static::assertEquals('translatable', $info['translation_strategy']);
        static::assertEquals(['translations'], $info['includes']['default']);

        static::assertCount(10, $info['attributes'], 'Incorrect attribute count');
        static::assertArrayHasKey('id', $info['attributes']);
        static::assertArrayHasKey('test_author_id', $info['attributes']);
        static::assertArrayHasKey('test_genre_id', $info['attributes']);
        static::assertArrayHasKey('description', $info['attributes']);
        static::assertArrayHasKey('type', $info['attributes']);
        static::assertArrayHasKey('checked', $info['attributes']);
        static::assertArrayHasKey('created_at', $info['attributes']);
        static::assertArrayHasKey('updated_at', $info['attributes']);
        static::assertArrayHasKey('title', $info['attributes']);
        static::assertArrayHasKey('body', $info['attributes']);

        static::assertCount(3, $info['relations'], 'Incorrect relation count');
        static::assertArrayHasKey('author', $info['relations']);
        static::assertArrayHasKey('comments', $info['relations']);
        static::assertArrayHasKey('seo', $info['relations']);
        // Note that despite the genre_id column, there is indeed no genre relation defined.

        // Inexhaustive attribute checks
        static::assertTrue($info['attributes']['title']['translated']);
        static::assertTrue($info['attributes']['body']['translated']);
        static::assertEquals(AttributeCast::BOOLEAN, $info['attributes']['checked']['cast']);
        static::assertEquals('varchar', $info['attributes']['title']['type']);
        static::assertEquals('text', $info['attributes']['body']['type']);

        // Inexhaustive relation checks
        static::assertEquals(RelationType::BELONGS_TO, $info['relations']['author']['type']);
        static::assertEquals(RelationType::HAS_MANY, $info['relations']['comments']['type']);
        static::assertEquals(RelationType::MORPH_ONE, $info['relations']['seo']['type']);
    }

}
