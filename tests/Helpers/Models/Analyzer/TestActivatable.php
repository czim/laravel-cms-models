<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestActivatable extends Model
{
    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

}
