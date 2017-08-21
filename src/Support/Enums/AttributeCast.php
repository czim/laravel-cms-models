<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class AttributeCast extends Enum
{
    const BOOLEAN    = 'bool';
    const INTEGER    = 'int';
    const FLOAT      = 'float';
    const STRING     = 'string';
    const DATE       = 'date';
    const ARRAY_CAST = 'array';
    const JSON       = 'json';

    // Special casts
    const STAPLER_ATTACHMENT   = 'stapler-attachment';
    const PAPERCLIP_ATTACHMENT = 'paperclip-attachment';
}
