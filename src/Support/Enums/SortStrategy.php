<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class SortStrategy extends Enum
{
    const NULL_LAST       = 'null-last';
    const NULL_LAST_EMPTY = 'null-last-empty';
    const TRANSLATED      = 'translated';
}
