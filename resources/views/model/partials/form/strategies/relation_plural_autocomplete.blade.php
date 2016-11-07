
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       multiple="multiple"
       @if ($required && ! $translated) required="required" @endif
>

    @if ($value)
        @foreach ($value as $singleValue)
            <option value="{{ $singleValue }}" selected="selected">
                {{ array_get($references, $singleValue, $singleValue) }}
            </option>
        @endforeach
    @endif

</select>


@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))


@push('javascript-end')
    <!-- form field display strategy: relation plural autocomplete -->
    <script>
        $(function() {
            $('#field-{{ $key }}').select2({
                ajax: {
                    headers    : {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url      : '{{ cms_route('models-meta.references') }}',
                    type     : 'POST',
                    dataType : 'json',
                    delay    : 250,

                    data     : function (params) {
                        return {
                            model : '{{ str_replace('\\', '\\\\', get_class($record)) }}',
                            type  : 'form.field',
                            key   : '{{ $key }}',
                            search: params.term
                        };
                    },

                    processResults: function (data, params) {

                        // Convert the key/reference pairs from the model meta controller
                        // to the id/text pairs expected by select2.

                        var converted = [];

                        $.each(data, function (key, value) {
                            converted.push({
                                id   : value.key,
                                text : value.reference
                            })
                        });

                        return {
                            results: converted
                        };
                    },
                    cache   : false
                },

                minimumInputLength: 1
            });
        });
    </script>
@endpush