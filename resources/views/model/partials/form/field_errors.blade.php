
{{--Translated fields show errors in the container, not per field--}}
@if ( ! isset($translated) || ! $translated)

    <span id="{{ $key }}-errors" class="help-block">

        @if ($errors && count($errors))
            @foreach ($errors as $error)
                {{--TODO: Refactor--}}
                @if (is_array($error))
                    @foreach ($error as $_error)
                        {{ $_error }}<br>
                    @endforeach
                @else
                    {{ $error }}<br>
                @endif
            @endforeach
        @endif
    </span>

@endif
