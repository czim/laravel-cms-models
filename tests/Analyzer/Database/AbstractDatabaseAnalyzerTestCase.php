<?php
namespace Czim\CmsModels\Test\Analyzer\Database;

use Czim\CmsModels\Test\DatabaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class AbstractDatabaseAnalyzerTestCase extends DatabaseTestCase
{

    protected function migrateDatabase()
    {
        Schema::dropIfExists('test_columns');
        Schema::create('test_columns', function($table) {
            $table->increments('id');
            $table->enum('enum', [ 'a', 'b' ])->default('b');
            $table->string('string', 128);
            $table->string('nullable_string', 255)->nullable();
            $table->text('text')->nullable();
            $table->boolean('bool')->default(false);
            $table->integer('integer')->nullable();
            $table->decimal('decimal', 6, 2);
            $table->date('date')->nullable();
            $table->datetime('datetime')->nullable();
            $table->timestamp('timestamp')->nullable();
        });
    }

}
