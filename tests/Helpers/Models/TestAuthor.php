<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Codesleeve\Stapler\ORM\StaplerableInterface;
use Czim\CmsModels\Test\Helpers\Models\Stapler\EloquentTraitFixed;
use Czim\Listify\Listify;
use Illuminate\Database\Eloquent\Model;

class TestAuthor extends Model implements StaplerableInterface
{
    use EloquentTraitFixed,
        Listify;

    protected $fillable = [
        'name',
        'image',
        'position',
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
