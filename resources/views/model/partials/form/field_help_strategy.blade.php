@if ($help)

    @php
        $partial = $help->view() ?: 'cms-models::model.partials.form.field_help';
    @endphp

    @include($partial, [
        'id'     => 'field-' . $type . '-helptext-' . $key,
        'text'   => $help->text(),
        'class'  => $help->cssClass(),
        'escape' => $help->escape(),
    ])
@endif  
