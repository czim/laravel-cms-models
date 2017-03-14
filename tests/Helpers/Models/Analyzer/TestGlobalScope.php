<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Czim\CmsModels\Test\Helpers\Models\Analyzer\Scopes\TestActiveScope;
use Illuminate\Database\Eloquent\Model;

class TestGlobalScope extends Model
{
    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TestActiveScope());
    }

}
