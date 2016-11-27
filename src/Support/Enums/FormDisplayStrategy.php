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
    const DROPDOWN   = 'dropdown';

    const DATEPICKER_DATE     = 'datepicker-date';
    const DATEPICKER_TIME     = 'datepicker-time';
    const DATEPICKER_DATETIME = 'datepicker-datetime';

    const ATTACHMENT_STAPLER_FILE  = 'attachment-stapler-file';
    const ATTACHMENT_STAPLER_IMAGE = 'attachment-stapler-image';

    // Relations
    const RELATION_SINGLE_DROPDOWN     = 'relation-single-dropdown';
    const RELATION_SINGLE_AUTOCOMPLETE = 'relation-single-autocomplete';
    const RELATION_PLURAL_MULTISELECT  = 'relation-plural-multiselect';
    const RELATION_PLURAL_AUTOCOMPLETE = 'relation-plural-autocomplete';
    const RELATION_PIVOT_ORDERABLE     = 'relation-pivot-orderable';

    const RELATION_SINGLE_MORPH_DROPDOWN     = 'relation-single-morph-dropdown';
    const RELATION_SINGLE_MORPH_AUTOCOMPLETE = 'relation-single-morph-autocomplete';
}
