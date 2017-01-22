<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class FilterStrategy extends Enum
{
    const BOOLEAN      = 'boolean';
    const DATE         = 'date';
    const DROPDOWN     = 'dropdown';
    const STRING       = 'string';
    const STRING_SPLIT = 'string-split';
}
