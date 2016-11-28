
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       @if ($required && ! $translated) required="required" @endif
>
    @if ( ! $required)
        <option></option>
    @endif

    @if ($value)
        <option value="{{ $value }}" selected="selected">
            {{ array_get($references, $value, $value) }}
        </option>
    @endif

</select>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@push('javascript-end')
    <!-- form field display strategy: relation single autocomplete -->
    <script>
        $(function() {

            /**
             * Returns the display label for a given model class.
             *
             * @param string modelClass
             * @return string
             */
            var getReferenceForModelClass = function(modelClass) {
                console.log(modelClass);
                switch (modelClass) {
    @foreach ($modelLabels as $modelClass => $label)
                case "{{ str_replace('\\', '\\\\', $modelClass) }}":
                        return "{{ str_replace('"', '\\"', $label) }}";
    @endforeach

                    default: return modelClass;
                }
            };

            $('#field-{{ $key }}').select2({
                placeholder : '--',
                allowClear  : {{ $required ? 'false' : 'true' }},
                ajax        : {
                    headers   : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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

                            var modelClass = key;
                                label      = getReferenceForModelClass(modelClass),
                                options    = [];

                            $.each(value, function (key, value) {
                                options.push({
                                    id  : modelClass + ':' + value.key,
                                    text: value.reference
                                });
                            });

                            if (options.length) {
                                converted.push({
                                    text    : label,
                                    children: options
                                });
                            }
                        });

                        return {
                            results: converted
                        };
                    },
                    cache: false
                },

                minimumInputLength: 1
            });
        });
    </script>
@endpush
