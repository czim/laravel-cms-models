<span id="{{ $id }}" class="help-block {{ $class }}">
    @if ($escape)
        {{ $text }}
    @else
        {!! $text !!}
    @endif
</span>
