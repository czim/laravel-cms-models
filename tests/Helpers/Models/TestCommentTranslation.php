<?php
namespace Czim\CmsModels\Test\Helpers\Models;

use Illuminate\Database\Eloquent\Model;

class TestCommentTranslation extends Model
{
    protected $fillable = [
        'title',
        'body',
    ];

}
