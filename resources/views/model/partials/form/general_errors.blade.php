
{{-- Expanded error section with full form errors --}}

<ul>
@foreach ($errors->toArray() as $key => $error)

    {{-- Skip built in general errors --}}
    @continue($key == '__general__')

    <li>
        <b>{{ $key }}</b>:
        <br>

        @if (is_array($error))
            @foreach ($error as $_error)
                {{ $_error }}<br>
            @endforeach
        @else
            {{ $error }}<br>
        @endif
    </li>

@endforeach
</ul>
