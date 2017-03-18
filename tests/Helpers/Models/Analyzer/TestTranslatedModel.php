<?php
namespace Czim\CmsModels\Test\Helpers\Models\Analyzer;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class TestTranslatedModel extends Model
{
    use Translatable;

    protected $fillable = [
        'name',
        'title',
        'description',
    ];

    protected $translatedAttributes = [
        'title',
        'description',
    ];

}
