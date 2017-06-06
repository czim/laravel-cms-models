
@if ( ! ($parent instanceof \Czim\CmsModels\ModelInformation\Data\Form\Layout\ModelFormFieldGroupData))
    <div class="form-group row @if (array_has($errors, $key)) has-error @endif">

        <label for="field-{{ $key }}" class="control-label col-sm-2 @if ($field->required()) required @endif">
            <span class="field-label">
                {{ $field->label() }}
            </span>

            @if ($field->help)
                @include('cms-models::model.partials.form.field_help_strategy', [
                    'type' => 'label',
                    'help' => $field->help->label,
                    'key'  => $key,
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

        @if ($field->help)
            @include('cms-models::model.partials.form.field_help_strategy', [
                'type' => 'field',
                'help' => $field->help->field,
                'key'  => $key,
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
