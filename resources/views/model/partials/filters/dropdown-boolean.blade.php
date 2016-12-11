
<div class="form-group">
    <select id="filter.{{ $key }}" name="filter[{{ $key }}]" class="form-control input-sm">

        <option value=""
                @if (null === $selected)
                    selected="selected"
                @endif
            ></option>

        @foreach ($options as $option => $display)

            <option value="{{ $option }}"
                    @if (null !== $selected && $selected == $option)
                        selected="selected"
                    @endif
                >
                {{ $display }}
            </option>
        @endforeach
    </select>
</div>
