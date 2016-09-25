<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class OrderablePosition extends Enum
{
    const UP     = 'up';
    const DOWN   = 'down';
    const BOTTOM = 'bottom';
    const TOP    = 'top';
    const REMOVE = 'remove';    // remove if from the list (same as NULL)
}
