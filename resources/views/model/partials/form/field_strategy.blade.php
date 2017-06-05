
@if ( ! ($parent instanceof \Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData))
    <div class="form-group row @if (array_has($errors, $key)) has-error @endif">

        <label for="field-{{ $key }}" class="control-label col-sm-2 @if ($field->required()) required @endif">
            <span class="field-label">
                {{ $field->label() }}
            </span>

            {{-- Help text --}}
            @if ($field->help->label)
                @php
                    $partial = $field->help->label->view() ?: 'cms-models::model.partials.form.field_help';
                @endphp
                @include($partial, [
                    'id'     => 'field-label-helptext-' . $key,
                    'text'   => $field->help->label->text(),
                    'class'  => $field->help->label->cssClass(),
                    'escape' => $field->help->label->escape(),
                ])
            @endif
        </label>
@endif

    <div class="col-sm-{{ isset($columnWidth) ? $columnWidth : 10 }}">

        {{-- Before view --}}
        @if ($field->before && $field->before->view)
            @include($field->before->view, $field->before->variables())
        @endif

        {!! $strategy !!}

        {{-- Help text --}}
        @if ($field->help->field)
            @php
                $partial = $field->help->field->view() ?: 'cms-models::model.partials.form.field_help';
            @endphp
            @include($partial, [
                'id'     => 'field-helptext-' . $key,
                'text'   => $field->help->field->text(),
                'class'  => $field->help->field->cssClass(),
                'escape' => $field->help->field->escape(),
            ])
        @endif

        {{-- After view --}}
        @if ($field->after && $field->after->view)
            @include($field->after->view, $field->after->variables())
        @endif
    </div>

@if ( ! ($parent instanceof \Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData))
</div>
@endif
