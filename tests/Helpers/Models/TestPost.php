<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TestPost
 *
 * @property string $title
 * @property string $body
 * @property string $type
 * @property bool   $checked
 * @property string $description
 */
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

    public $test = false;


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

    /**
     * @return string
     */
    public function testMethod()
    {
        return 'testing method value';
    }

    /**
     * @param mixed $value
     */
    public function testSetValue($value)
    {
        $this->test = $value;
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeChecked($query)
    {
        return $query->where('checked', true);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeNotice($query)
    {
        return $query->where('type', 'notice');
    }

}
