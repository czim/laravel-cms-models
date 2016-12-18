
<div class="form-group row">

    <label for="field-{{ $key }}" class="control-label col-sm-2">
        {{ $field->label() }}
    </label>

    <div class="col-sm-10">
        {!! $strategy->render($record, $field->source) !!}
    </div>

</div>
