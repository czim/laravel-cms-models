<?php
namespace Czim\CmsModels\Test;

use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\ModelInformation\Analyzer\Database\SimpleDatabaseAnalyzer;

abstract class DatabaseTestCase extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.default', 'testbench');

        $this->setDatabaseConnectionConfig($app);
        $this->extraDatabaseSetup($app);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setDatabaseConnectionConfig($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function extraDatabaseSetup($app)
    {
        $app->bind(DatabaseAnalyzerInterface::class, SimpleDatabaseAnalyzer::class);
    }


    public function setUp()
    {
        parent::setUp();

        $this->migrateDatabase();
        $this->seedDatabase();
    }


    protected function migrateDatabase()
    {
    }

    protected function seedDatabase()
    {
    }

}
