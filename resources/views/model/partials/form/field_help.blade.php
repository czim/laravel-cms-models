<span id="{{ $id }}" class="help-block {{ $class }}">

    @if ($icon)
        <i class="fa fa-{{ $icon }}"></i>
    @endif

    @if ($escape)
        {{ $text }}
    @else
        {!! $text !!}
    @endif
</span>
