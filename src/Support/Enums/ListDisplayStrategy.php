<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class ListDisplayStrategy extends Enum
{
    const CHECK          = 'check';
    const CHECK_NULLABLE = 'check-nullable';

    const STAPLER_FILENAME  = 'stapler-filename';
    const STAPLER_THUMBNAIL = 'stapler-thumbnail';

    // Relations

    const RELATION_COUNT     = 'relation-count';
    const RELATION_REFERENCE = 'relation-reference';
}
