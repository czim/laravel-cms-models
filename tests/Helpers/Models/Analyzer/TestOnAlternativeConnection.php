<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestOnAlternativeConnection extends Model
{
    protected $connection = 'testbench_alt';

    protected $fillable = [
        'name',
    ];

}
