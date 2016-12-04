<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class LayoutNodeType extends Enum
{
    const TAB      = 'tab';
    const FIELDSET = 'fieldset';
    const GROUP    = 'group';
    const LABEL    = 'label';
}
