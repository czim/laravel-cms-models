
<select id="field-{{ $key }}"
       name="{{ $name ?: $key }}"
       class="form-control select2"
       @if ($required && ! $translated) required="required" @endif
>
    @if ($value)

        @if (is_array($value))
            {{-- value as a key/reference pair --}}
            <option value="{{ array_get($value, 'key') }}" selected="selected">{{ array_get($value, 'reference') }}</option>
        @else
            {{-- value as only a key --}}
            <option value="{{ $value }}" selected="selected">{{ $value }}</option>
        @endif
    @endif

</select>

@include('cms-models::model.partials.form.field_errors', compact('key', 'errors'))

@push('javascript-end')
    <script>
        $(function() {
        });
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

                    // let our custom formatter work
//                escapeMarkup: function (markup) { return markup; }

                    // omitted for brevity, see the source of this page
//                templateResult     : formatRepo,

                    // omitted for brevity, see the source of this page
//                templateSelection  : formatRepoSelection
                });
    </script>

<?php /*
    <script>
        $('#field-{{ $key }}').selectize({
            valueField      : 'key',
            labelField      : 'value',
            searchField     : 'value',
            maxItems        : 1,
            allowEmptyOption: true,
            persist         : false,
            loadThrottle    : 600,
            create          : false,
            createOnBlur    : true,
            sortField       : 'text',
            options         : [],

            load: function (query, callback) {
                if ( ! query.length) return callback();
                $.ajax({
                    headers    : {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url        : '{{ cms_route('models-meta.references') }}',
                    type       : 'POST',
                    dataType   : 'json',
                    data       : {
                        model  : '{{ str_replace('\\', '\\\\', get_class($record)) }}',
                        type   : 'form.field',
                        key    : '{{ $key }}',
                        search : query
                    },
                    error: function () {
                        callback();
                    },
                    success: function (res) {
                        callback(res);
                    }
                });
            }
        });
    </script>
 */ ?>
@endpush
