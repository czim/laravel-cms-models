<?php
namespace Czim\CmsModels\Test\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps\AnalyzeAttributes;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Database\Eloquent\Model;
use Mockery;

/**
 * Class AnalyzeAttributesTest
 *
 * @group analysis
 */
class AnalyzeAttributesTest extends AbstractStepCase
{

    /**
     * @test
     */
    function it_analyzes_attributes_from_the_database()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'id',
                'type'     => 'int',
                'length'   => 10,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'title',
                'type'     => 'varchar',
                'length'   => 255,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'description',
                'type'     => 'text',
                'length'   => 1024,
                'values'   => [],
                'unsigned' => false,
                'nullable' => true,
            ],
            [
                'name'     => 'type',
                'type'     => 'enum',
                'length'   => null,
                'values'   => ['test1', 'test2', 'test3'],
                'unsigned' => false,
                'nullable' => true,
            ],
            [
                'name'     => 'active',
                'type'     => 'tinyint',
                'length'   => 1,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
        ]);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertInternalType('array', $info['attributes']);
        static::assertEquals(['id', 'title', 'description', 'type', 'active'], array_keys($info['attributes']));

        /** @var ModelAttributeData $attribute */
        $attribute = $info['attributes']['id'];
        static::assertInstanceOf(ModelAttributeData::class, $attribute);
        static::assertEquals(AttributeCast::INTEGER, $attribute->cast);
        static::assertEquals('int', $attribute->type);
        static::assertEquals(false, $attribute->fillable);
        static::assertEquals(false, $attribute->hidden);
        static::assertEquals(false, $attribute->translated);
        static::assertEquals(10, $attribute->length);
        static::assertEquals(false, $attribute->nullable);
        static::assertEquals(true, $attribute->unsigned);

        $attribute = $info['attributes']['title'];
        static::assertInstanceOf(ModelAttributeData::class, $attribute);
        static::assertEquals(AttributeCast::STRING, $attribute->cast);
        static::assertEquals('varchar', $attribute->type);
        static::assertEquals(true, $attribute->fillable);
        static::assertEquals(false, $attribute->hidden);
        static::assertEquals(false, $attribute->translated);
        static::assertEquals(255, $attribute->length);
        static::assertEquals(false, $attribute->nullable);

        $attribute = $info['attributes']['description'];
        static::assertInstanceOf(ModelAttributeData::class, $attribute);
        static::assertEquals(AttributeCast::STRING, $attribute->cast);
        static::assertEquals('text', $attribute->type);
        static::assertEquals(true, $attribute->fillable);
        static::assertEquals(false, $attribute->hidden);
        static::assertEquals(false, $attribute->translated);
        static::assertEquals(1024, $attribute->length);
        static::assertEquals(true, $attribute->nullable);

        $attribute = $info['attributes']['type'];
        static::assertInstanceOf(ModelAttributeData::class, $attribute);
        static::assertEquals(AttributeCast::STRING, $attribute->cast);
        static::assertEquals('enum', $attribute->type);
        static::assertEquals(true, $attribute->fillable);
        static::assertEquals(false, $attribute->hidden);
        static::assertEquals(false, $attribute->translated);
        static::assertEquals(true, $attribute->nullable);
        static::assertEquals(['test1', 'test2', 'test3'], $attribute->values);

        $attribute = $info['attributes']['active'];
        static::assertInstanceOf(ModelAttributeData::class, $attribute);
        static::assertEquals(AttributeCast::BOOLEAN, $attribute->cast);
        static::assertEquals('tinyint', $attribute->type);
        static::assertEquals(false, $attribute->fillable);
        static::assertEquals(false, $attribute->hidden);
        static::assertEquals(false, $attribute->translated);
        static::assertEquals(1, $attribute->length);
        static::assertEquals(false, $attribute->nullable);
        static::assertEquals(true, $attribute->unsigned);
    }
    
    /**
     * @test
     */
    function it_normalizes_boolean_types_correctly()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'bool',
                'type'     => 'bool',
                'length'   => 1,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'tinyint',
                'type'     => 'tinyint',
                'length'   => 1,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::BOOLEAN, $info['attributes']['bool']->cast);
        static::assertEquals(AttributeCast::BOOLEAN, $info['attributes']['tinyint']->cast);
    }

    /**
     * @test
     */
    function it_normalizes_integer_types_correctly()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'int',
                'type'     => 'int',
                'length'   => 10,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'tinyint',
                'type'     => 'tinyint',
                'length'   => 4,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'smallint',
                'type'     => 'smallint',
                'length'   => 14,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'mediumint',
                'type'     => 'mediumint',
                'length'   => 18,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'bigint',
                'type'     => 'bigint',
                'length'   => 18,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['int']->cast);
        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['tinyint']->cast);
        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['smallint']->cast);
        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['mediumint']->cast);
        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['bigint']->cast);
    }

    /**
     * @test
     */
    function it_normalizes_decimal_types_correctly()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'decimal',
                'type'     => 'decimal',
                'length'   => 16,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'float',
                'type'     => 'float',
                'length'   => 12,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'dec',
                'type'     => 'dec',
                'length'   => 14,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
            [
                'name'     => 'double',
                'type'     => 'double',
                'length'   => 16,
                'values'   => [],
                'unsigned' => true,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['decimal']->cast);
        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['float']->cast);
        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['dec']->cast);
        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['double']->cast);
    }

    /**
     * @test
     */
    function it_normalizes_string_and_blob_types_correctly()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'varchar',
                'type'     => 'varchar',
                'length'   => 255,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'binary',
                'type'     => 'binary',
                'length'   => 1024,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'blob',
                'type'     => 'blob',
                'length'   => 1024,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'year',
                'type'     => 'year',
                'length'   => 4,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'varbinary',
                'type'     => 'varbinary',
                'length'   => 1024,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::STRING, $info['attributes']['varchar']->cast);
        static::assertEquals(AttributeCast::STRING, $info['attributes']['binary']->cast);
        static::assertEquals(AttributeCast::STRING, $info['attributes']['blob']->cast);
        static::assertEquals(AttributeCast::STRING, $info['attributes']['year']->cast);
        static::assertEquals(AttributeCast::STRING, $info['attributes']['varbinary']->cast);
    }

    /**
     * @test
     */
    function it_normalizes_date_types_correctly()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'date',
                'type'     => 'date',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'datetime',
                'type'     => 'datetime',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'time',
                'type'     => 'time',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'timestamp',
                'type'     => 'timestamp',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::DATE, $info['attributes']['date']->cast);
        static::assertEquals(AttributeCast::DATE, $info['attributes']['datetime']->cast);
        static::assertEquals(AttributeCast::DATE, $info['attributes']['time']->cast);
        static::assertEquals(AttributeCast::DATE, $info['attributes']['timestamp']->cast);
    }

    /**
     * @test
     */
    function it_keeps_the_database_type_as_cast_when_it_cannot_normalize()
    {
        // Setup
        $model    = new TestPost;
        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'mystery',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals('unknown', $info['attributes']['mystery']->cast);
    }

    /**
     * @test
     */
    function it_overwrites_database_analyzed_casts_with_model_defined_casts()
    {
        // Setup
        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(TestPost::class . '[getCasts]');
        $model->shouldReceive('getCasts')->andReturn([
            'mystery' => 'float',
        ]);

        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'mystery',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['mystery']->cast);
    }

    /**
     * @test
     */
    function it_normalizes_model_defined_casts()
    {
        // Setup
        /** @var Model|Mockery\Mock $model */
        $model = Mockery::mock(TestPost::class . '[getCasts]');
        $model->shouldReceive('getCasts')->andReturn([
            'a' => 'boolean',
            'b' => 'integer',
            'c' => 'decimal',
            'd' => 'datetime',
        ]);

        $analyzer = $this->prepareAnalyzerSetup($model);

        $dbMock = $this->prepareMockDatabaseAnalyzer();
        $dbMock->shouldReceive('getColumns')->andReturn([
            [
                'name'     => 'a',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'b',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'c',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
            [
                'name'     => 'd',
                'type'     => 'unknown',
                'length'   => null,
                'values'   => [],
                'unsigned' => false,
                'nullable' => false,
            ],
        ]);

        $this->app->instance(DatabaseAnalyzerInterface::class, $dbMock);

        $info = new ModelInformation;
        $info->model          = get_class($model);
        $info->original_model = $info->model;

        // Test
        $step = new AnalyzeAttributes;
        $step->setAnalyzer($analyzer);

        $info = $step->analyze($info);

        static::assertEquals(AttributeCast::BOOLEAN, $info['attributes']['a']->cast);
        static::assertEquals(AttributeCast::INTEGER, $info['attributes']['b']->cast);
        static::assertEquals(AttributeCast::FLOAT, $info['attributes']['c']->cast);
        static::assertEquals(AttributeCast::DATE, $info['attributes']['d']->cast);
    }
    

    /**
     * @return DatabaseAnalyzerInterface|Mockery\MockInterface|Mockery\Mock
     */
    protected function prepareMockDatabaseAnalyzer()
    {
        $mock = Mockery::mock(DatabaseAnalyzerInterface::class);

        // Let driver-mapped analyzer instantiation fall back to the interface binding
        $this->app['config']->set('cms-models.analyzer.database.driver', []);

        $this->app->instance(DatabaseAnalyzerInterface::class, $mock);

        return $mock;
    }

}
