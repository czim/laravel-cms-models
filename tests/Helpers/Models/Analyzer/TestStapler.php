<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\Model;

class TestStapler extends Model implements StaplerableInterface
{
    use EloquentTrait;

    protected $fillable = [
        'name',
        'file',
        'image',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('file');
        $this->hasAttachedFile('image', [
            'styles' => [
                'medium' => '300x300',
                'thumb'  => '100x100'
            ]
        ]);

        parent::__construct($attributes);
    }

}
