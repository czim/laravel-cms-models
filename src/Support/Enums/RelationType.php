<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class RelationType extends Enum
{

    const BELONGS_TO      = 'belongsTo';
    const BELONGS_TO_MANY = 'belongsToMany';
    const HAS_MANY        = 'hasMany';
    const HAS_ONE         = 'hasOne';
    const MORPH_ONE       = 'morphOne';
    const MORPH_MANY      = 'morphMany';
    const MORPH_TO        = 'morphTo';
    const MORPH_TO_MANY   = 'morphToMany';
    const MORPHED_BY_MANY = 'morphedByMany';

    const BELONGS_TO_THROUGH  = 'belongsToThrough';

}
