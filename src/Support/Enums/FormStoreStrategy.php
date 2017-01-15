<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class FormStoreStrategy extends Enum
{
    const BOOLEAN                = 'boolean';
    const DATE                   = 'date';
    const DATE_RANGE             = 'date-range';
    const LOCATION_FIELDS        = 'location-fields';
    const STAPLER                = 'stapler';
    const TAGGABLE               = 'taggable';
    const RELATION_SINGLE_KEY    = 'relation-single-key';
    const RELATION_PLURAL_KEYS   = 'relation-plural-key';
    const RELATION_PIVOT_ORDERED = 'relation-pivot-ordered';
    const RELATION_SINGLE_MORPH  = 'relation-single-morph';
}
