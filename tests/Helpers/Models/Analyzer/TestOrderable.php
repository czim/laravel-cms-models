<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Czim\Listify\Listify;
use Illuminate\Database\Eloquent\Model;

class TestOrderable extends Model
{
    use Listify;

    protected $fillable = [
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

}
