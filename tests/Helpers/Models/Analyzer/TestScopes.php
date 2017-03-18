<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Illuminate\Database\Eloquent\Model;

class TestScopes extends Model
{
    protected $fillable = [
        'name',
    ];

    protected function scopeNormal($query)
    {
    }

    protected function scopeNotScopeBecauseOfParameters($query, $required)
    {
    }

    protected function scopeNotScopeBecauseFirstParameterIsNotRequired($query = null, $required)
    {
    }

    /**
     * @param $query
     * @cms ignore
     */
    protected function scopeIgnoredScope($query)
    {
    }

}
