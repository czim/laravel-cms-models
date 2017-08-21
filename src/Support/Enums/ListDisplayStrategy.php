<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class ListDisplayStrategy extends Enum
{
    const CHECK          = 'check';
    const CHECK_NULLABLE = 'check-nullable';

    const DATE     = 'date';
    const TIME     = 'time';
    const DATETIME = 'datetime';
    const TAGS     = 'tags';

    const STAPLER_FILENAME    = 'stapler-filename';
    const STAPLER_THUMBNAIL   = 'stapler-thumbnail';
    const PAPERCLIP_FILENAME  = 'paperclip-filename';
    const PAPERCLIP_THUMBNAIL = 'paperclip-thumbnail';

    // Relations

    const RELATION_COUNT          = 'relation-count';
    const RELATION_REFERENCE      = 'relation-reference';
    const RELATION_REFERENCE_LINK = 'relation-reference-link';
    const RELATION_COUNT_LINK     = 'relation-count-link';
}
