
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

            <option value="{{ $singleValue }}" selected="selected">{{ $singleValue }}</option>
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
    @php
        $tagData = array_map(function ($tag) { return [ 'id' => $tag, 'text' => $tag ]; }, $tags ?: []);
    @endphp

    <script>
        $(function() {
            $('#field-{{ $key }}').select2({
                width              : '100%',
                tags               : true,
                placeholder        : '--',
                data               : {!! json_encode($tagData) !!},
                minimumInputLength : {{ $minimumInputLength }}
            });
        });
    </script>
@cms_endscript
