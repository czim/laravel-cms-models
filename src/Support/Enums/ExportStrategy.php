<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class ExportStrategy extends Enum
{
    const CSV   = 'csv';
    const EXCEL = 'excel';
    const XML   = 'xml';
}
