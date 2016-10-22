
<span id="{{ $key }}-errors" class="help-block">

    @if ($errors && count($errors))
        @foreach ($errors as $error)
            {{ $error }}<br>
        @endforeach
    @endif
</span>
