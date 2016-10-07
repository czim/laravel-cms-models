
<div class="form-group row">

    @if ( ! ($parent instanceof \Czim\CmsModels\Support\Data\ModelFormFieldGroupData))
        <label for="field-{{ $key }}" class="col-sm-2">
            {{ $field->label() }}
        </label>
    @endif

    <div class="col-sm-10">

        <?php
            $strategy = app(\Czim\CmsModels\Contracts\View\FormFieldStrategyInterface::class);
        ?>

        {!! $strategy->render(
            $record,
            $field,
            null, // value
            [] // errors
        ) !!}
    </div>

</div>
