
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}[]"
       class="form-control select2"
       multiple="multiple"
       @if ($required && ! $translated) required="required" @endif
>
    @if ( ! $required)
        <option></option>
    @endif

    @if ($value)
        @foreach ($value as $singleValue)
            @continue (null === $singleValue)

            <option value="{{ $singleValue }}" selected="selected">
                {{ array_get($references, $singleValue, $singleValue) }}
            </option>
        @endforeach
    @endif

</select>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@cms_script
    <!-- form field display strategy: relation plural autocomplete -->
    <script>
        $(function() {
            $('#field-{{ $key }}').select2({
                width       : '100%',
                placeholder : '--',
                allowClear  : {{ $required ? 'false' : 'true' }},
                ajax        : {
                    headers    : {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url       : '{{ cms_route('models-meta.references') }}',
                    type      : 'POST',
                    dataType  : 'json',
                    delay     : 250,

                    data: function (params) {
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

                minimumInputLength: {{ $minimumInputLength }}
            });
        });
    </script>
@cms_endscript
