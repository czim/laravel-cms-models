<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class AttributeFormStrategy extends Enum
{

    const BOOLEAN_CHECKBOX = 'booleanCheckbox';
    const BOOLEAN_DROPDOWN = 'booleanDropdown';

    const NUMERIC_INTEGER = 'numericInteger';
    const NUMERIC_DECIMAL = 'numericDecimal';
    const NUMERIC_YEAR    = 'numericYear';

    const TEXT     = 'text';
    const TEXTAREA = 'textarea';

    const SELECT_DROPDOWN = 'selectDropdown';

    const DATEPICKER_DATE     = 'datepickerDate';
    const DATEPICKER_TIME     = 'datepickerTime';
    const DATEPICKER_DATETIME = 'datepickerDatetime';

    const ATTACHMENT_STAPLER_FILE  = 'attachmentStaplerFile';
    const ATTACHMENT_STAPLER_IMAGE = 'attachmentStaplerImage';
}
