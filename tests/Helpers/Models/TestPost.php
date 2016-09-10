<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class TestPost extends Model
{
    use Translatable;

    protected $fillable = [
        'title',
        'body',
        'type',
        'checked',
        'description',
    ];

    protected $translatedAttributes = [
        'title',
        'body',
    ];

    protected $casts = [
        'checked' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(TestAuthor::class, 'test_author_id');
    }

    public function comments()
    {
        return $this->hasMany(TestComment::class);
    }

    public function seo()
    {
        return $this->morphOne(TestSeo::class, 'seoable');
    }

}
