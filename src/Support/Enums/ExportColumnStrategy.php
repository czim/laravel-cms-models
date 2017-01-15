<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class ExportColumnStrategy extends Enum
{
    const BOOLEAN_STRING    = 'boolean-string';
    const TAG_LIST          = 'tag-list';
    const STAPLER_FILE_LINK = 'attachment-stapler-file';
}
