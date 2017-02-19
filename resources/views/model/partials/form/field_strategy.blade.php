
@if ( ! ($parent instanceof \Czim\CmsModels\Support\Data\ModelFormFieldGroupData))
    <div class="form-group row @if (array_has($errors, $key)) has-error @endif">

        <label for="field-{{ $key }}" class="control-label col-sm-2 @if ($field->required()) required @endif">
            {{ $field->label() }}
        </label>
@endif

    <div class="col-sm-{{ isset($columnWidth) ? $columnWidth : 10 }}">
        {!! $strategy !!}
    </div>

@if ( ! ($parent instanceof \Czim\CmsModels\Support\Data\ModelFormFieldGroupData))
</div>
@endif
