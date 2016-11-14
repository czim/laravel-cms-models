
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

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))

@push('javascript-end')
    <!-- form field display strategy: relation single autocomplete -->
    <script>
        $(function() {

            $('#field-{{ $key }}').select2({
                ajax: {
                    headers    : {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url        : '{{ cms_route('models-meta.references') }}',
                    type       : 'POST',
                    dataType   : 'json',
                    delay      : 250,
                    placeHolder: '--',
                    allowclear : {{ $required ? 'false' : 'true' }},

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

                        // todo: translate into optgroups
                        console.log(data);

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

                // let our custom formatter work
//                escapeMarkup: function (markup) { return markup; }

                // omitted for brevity, see the source of this page
//                templateResult     : formatRepo,

                // omitted for brevity, see the source of this page
//                templateSelection  : formatRepoSelection
            });
        });
    </script>
@endpush
