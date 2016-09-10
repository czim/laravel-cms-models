<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class TestComment extends Model
{
    use Translatable;

    protected $fillable = [
        'title',
        'body',
        'description',
    ];

    protected $translatedAttributes = [
        'title',
        'body',
    ];

    public function author()
    {
        return $this->belongsTo(TestAuthor::class, 'test_author_id');
    }

    public function post()
    {
        return $this->belongsTo(TestPost::class, 'test_post_id');
    }

    public function seo()
    {
        return $this->morphOne(TestSeo::class, 'seoable');
    }

}
