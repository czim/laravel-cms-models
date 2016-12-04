
<div @if ( ! is_numeric($key)) id="field-label-{{ $key }}" @endif>

    <label class="control-label @if ($label->required()) required @endif">
        {{ $label->display() }}
    </label>

</div>

