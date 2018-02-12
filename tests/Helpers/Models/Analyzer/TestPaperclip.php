<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Model;

class TestPaperclip extends Model implements AttachableInterface
{
    use PaperclipTrait;

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
