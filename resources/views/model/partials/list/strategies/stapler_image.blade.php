@if ($exists)
    <img class="stapler-thumbnail" src="{{ $urlThumb }}" alt="{{ $filename }}" width="{{ $width }}" height="{{ $height }}">
@else
    <div class="missing-image" style="@if ($width) width: {{ $width }}px;@endif @if ($height) height: {{ $height }}px @endif"></div>
@endif

