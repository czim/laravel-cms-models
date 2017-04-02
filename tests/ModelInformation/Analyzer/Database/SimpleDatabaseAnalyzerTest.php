<?php
namespace Czim\CmsModels\Test\ModelInformation\Analyzer\Database;

use Czim\CmsModels\ModelInformation\Analyzer\Database\SimpleDatabaseAnalyzer;

/**
 * Class SimpleDatabaseAnalyzerTest
 *
 * @group analysis
 */
class SimpleDatabaseAnalyzerTest extends AbstractDatabaseAnalyzerTestCase
{

    /**
     * @test
     */
    function it_returns_column_information_for_a_table()
    {
        $analyzer = new SimpleDatabaseAnalyzer;
        $columns  = $analyzer->getColumns('test_columns');

        static::assertInternalType('array', $columns);
        static::assertCount(11, $columns);

        $keyed = array_combine(array_pluck($columns, 'name'), $columns);

        static::assertEquals(
            [
                'id', 'enum', 'string', 'nullable_string', 'text', 'bool',
                'integer', 'decimal', 'date', 'datetime', 'timestamp',
            ],
            array_keys($keyed)
        );

        static::assertEquals(
            [
                'name'     => 'id',
                'type'     => 'integer',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => false,
            ],
            $keyed['id']
        );
        static::assertEquals(
            [
                'name'     => 'enum',
                'type'     => 'string',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => false,
            ],
            $keyed['enum']
        );
        static::assertEquals(
            [
                'name'     => 'string',
                'type'     => 'string',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => false,
            ],
            $keyed['string']
        );
        static::assertEquals(
            [
                'name'     => 'nullable_string',
                'type'     => 'string',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['nullable_string']
        );
        static::assertEquals(
            [
                'name'     => 'text',
                'type'     => 'text',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['text']
        );
        static::assertEquals(
            [
                'name'     => 'bool',
                'type'     => 'boolean',
                'length'   => 1,
                'values'   => false,
                'unsigned' => false,
                'nullable' => false,
            ],
            $keyed['bool']
        );
        static::assertEquals(
            [
                'name'     => 'integer',
                'type'     => 'integer',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['integer']
        );
        static::assertEquals(
            [
                'name'     => 'decimal',
                'type'     => 'decimal',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => false,
            ],
            $keyed['decimal']
        );
        static::assertEquals(
            [
                'name'     => 'date',
                'type'     => 'date',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['date']
        );
        static::assertEquals(
            [
                'name'     => 'datetime',
                'type'     => 'datetime',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['datetime']
        );
        static::assertEquals(
            [
                'name'     => 'timestamp',
                'type'     => 'datetime',
                'length'   => null,
                'values'   => false,
                'unsigned' => false,
                'nullable' => true,
            ],
            $keyed['timestamp']
        );
    }

}
