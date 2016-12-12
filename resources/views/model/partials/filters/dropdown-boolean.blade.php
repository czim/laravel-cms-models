
<div class="form-group">
    <select id="filter.{{ $key }}" name="filter[{{ $key }}]" class="form-control input-sm placeholder">

        <option value="" class="placeholder"
                disabled="disabled"
                hidden="hidden"
                @if (null === $selected)
                    selected="selected"
                @endif
        >
            {{ ucfirst($label) }}
        </option>
        <option value=""></option>

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


