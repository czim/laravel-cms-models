<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestSeo extends Model
{
    protected $fillable = [
        'slug',
    ];

    /**
     * @cms morph \Czim\CmsModels\Test\Helpers\Models\TestPost
     * @cms morph \Czim\CmsModels\Test\Helpers\Models\TestComment
     */
    public function seoable()
    {
        return $this->morphTo('seoable');
    }

}
