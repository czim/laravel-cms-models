<?php
namespace Czim\CmsModels\Support\Enums;

use MyCLabs\Enum\Enum;

class FormDisplayStrategy extends Enum
{
    const BOOLEAN_CHECKBOX = 'boolean-checkbox';
    const BOOLEAN_DROPDOWN = 'boolean-dropdown';

    const NUMERIC_INTEGER = 'numeric-integer';
    const NUMERIC_DECIMAL = 'numeric-decimal';
    const NUMERIC_YEAR    = 'numeric-year';
    const NUMERIC_PRICE   = 'numeric-price';

    const TEXT       = 'text';
    const TEXT_EMAIL = 'text-email';
    const PASSWORD   = 'text';
    const TEXTAREA   = 'textarea';
    const WYSIWYG    = 'wysiwyg';

    const SELECT_DROPDOWN = 'select-dropdown';

    const DATEPICKER_DATE     = 'datepicker-date';
    const DATEPICKER_TIME     = 'datepicker-time';
    const DATEPICKER_DATETIME = 'datepicker-datetime';

    const ATTACHMENT_STAPLER_FILE  = 'attachment-stapler-file';
    const ATTACHMENT_STAPLER_IMAGE = 'attachment-stapler-image';

    // Relations

    const SELECT_DROPDOWN_BELONGS_TO = 'select-dropdown-belongs-to';
    const SELECT_DROPDOWN_HAS_ONE    = 'select-dropdown-has-one';
    const SELECT_MULTIPLE_HAS_MANY   = 'select-multiple-has-many';
}
