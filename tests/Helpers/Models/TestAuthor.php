<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\Model;

class TestAuthor extends Model implements StaplerableInterface
{
    use EloquentTrait;

    protected $fillable = [
        'name',
        'image',
    ];

    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('image', [
            'styles' => [
                'medium' => '300x300',
                'thumb'  => '100x100'
            ]
        ]);

        parent::__construct($attributes);
    }

    public function posts()
    {
        return $this->hasMany(TestPost::class);
    }

    public function comments()
    {
        return $this->hasMany(TestComment::class, 'seoable');
    }

}
