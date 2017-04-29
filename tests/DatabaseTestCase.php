<?php
namespace Czim\CmsModels\Test;

use Czim\CmsModels\Contracts\ModelInformation\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\ModelInformation\Analyzer\Database\SimpleDatabaseAnalyzer;
use Illuminate\Database\Schema\Builder;

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
        $app['config']->set('database.connections.testbench', $this->getDatabaseConfigForSqlite());
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setDatabaseConnectionConfigForMysql($app)
    {
        $app['config']->set('database.connections.testbench', $this->getDatabaseConfigForMysql());
    }

    /**
     * Returns the testing config array for a MySQL connection.
     *
     * @return array
     */
    protected function getDatabaseConfigForMysql()
    {
        return [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'testing',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
            'engine'    => null,
        ];
    }

    /**
     * Returns the testing config for a (shared) SQLite connection.
     *
     * @return array
     */
    protected function getDatabaseConfigForSqlite()
    {
        return [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ];
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

    protected function setMysqlDatabase()
    {

    }

    /**
     * @return Builder
     */
    protected function schema()
    {
        return $this->app['db']->connection()->getSchemaBuilder();
    }

}
