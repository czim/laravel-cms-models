<?php
namespace Czim\CmsModels\Test\Integration\Analyzer\Processor;

use Czim\CmsModels\ModelInformation\Analyzer\Processor\ModelAnalyzer;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use Czim\CmsModels\Test\Helpers\Models\Analyzer\TestOnAlternativeConnection;
use Czim\CmsModels\Test\Helpers\Models\TestComment;
use Czim\CmsModels\Test\Helpers\Models\TestPost;
use Illuminate\Support\Facades\Schema;

class ModelAnalysisConnectionSwitchTest extends AbstractAnalyzerTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function setDatabaseConnectionConfig($app)
    {
        // Make sure default connection is configured
        parent::setDatabaseConnectionConfig($app);

        // And add an alternative
        $app['config']->set('database.connections.testbench_alt', [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'testing',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
    }

    protected function migrateDatabase()
    {
        parent::migrateDatabase();

        // Also set up a single model on the alternative connection
        Schema::connection('testbench_alt')->dropIfExists('test_on_alternative_connections');
        Schema::connection('testbench_alt')->create('test_on_alternative_connections', function($table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->nullableTimestamps();
        });
    }

    /**
     * @test
     */
    function it_analyzes_models_on_their_configured_connection()
    {
        $analyzer = new ModelAnalyzer();

        // On default connection
        $info = $analyzer->analyze(TestPost::class);
        static::assertInstanceOf(ModelInformation::class, $info);
        static::assertEquals(TestPost::class, $info['model']);

        // On alternative connection
        $info = $analyzer->analyze(TestOnAlternativeConnection::class);
        static::assertInstanceOf(ModelInformation::class, $info);
        static::assertEquals(TestOnAlternativeConnection::class, $info['model']);

        // Back to default connection
        $info = $analyzer->analyze(TestComment::class);
        static::assertInstanceOf(ModelInformation::class, $info);
        static::assertEquals(TestComment::class, $info['model']);
    }

}
