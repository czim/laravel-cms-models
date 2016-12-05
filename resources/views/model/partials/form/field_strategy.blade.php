

<div class="form-group row @if (array_has($errors, $key)) has-error @endif">

    @if ( ! ($parent instanceof \Czim\CmsModels\Support\Data\ModelFormFieldGroupData))
        <label for="field-{{ $key }}" class="control-label col-sm-2 @if ($field->required()) required @endif">
            {{ $field->label() }}
        </label>
    @endif

    <div class="col-sm-10">

        <?php
            /** @var \Czim\CmsModels\Contracts\View\FormFieldDisplayInterface $strategy */

            $value = old() ? old($key) : array_get($values, $key);
        ?>

        {!! $strategy->render(
            $record,
            $field,
            $value,
            array_get($values, $key),
            array_get($errors, $key, [])
        ) !!}
    </div>

</div>
