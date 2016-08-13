<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class RelationFormStrategy extends Enum
{

    const BELONGS_TO_DROPDOWN = 'belongsToDropdown';
    const HAS_MANY_DROPDOWN   = 'hasManyDropdown';
    const HAS_ONE_DROPDOWN    = 'hasOneDropdown';
    const MORPH_ONE_DROPDOWN  = 'morphOneDropdown';

    const BELONGS_TO_AUTOCOMPLETE = 'belongsToAutocomplete';
    const HAS_MANY_AUTOCOMPLETE   = 'hasManyAutocomplete';
    const HAS_ONE_AUTOCOMPLETE    = 'hasOneAutocomplete';
    const MORPH_ONE_AUTOCOMPLETE  = 'morphOneAutocomplete';

    const BELONGS_TO_THROUGH_DROPDOWN = 'belongsToThroughDropdown';
}
