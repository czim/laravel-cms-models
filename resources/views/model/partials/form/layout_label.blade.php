
<label @if ( ! is_numeric($key)) id="field-label-{{ $key }}" @endif
       class="control-label @if ($label->required()) required @endif @if (isset($columnWidth)) col-sm-{{ $columnWidth }} @endif"
       @if ($label->labelFor()) for="field-{{ $label->labelFor() }}" @endif
>
    {{ $label->display() }}
</label>
