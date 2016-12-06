

<div class="form-control-static">

    @if (   ! ($original instanceof \Codesleeve\Stapler\Interfaces\Attachment)
        ||  null === $original->size()
        )

        <span class="text-muted">
            <em>{{ ucfirst(cms_trans('models.upload.nothing-uploaded')) }}</em>
        </span>

    @else

        <span class="text-primary" style="margin-right: 2em">
            <a href="{{ $original->url() }}" target="_blank">
                {{ $original->originalFilename() }}
            </a>
        </span>

        <span class="text-muted">
            {{ $original->contentType() }},
            {{ $original->size() }} bytes
        </span>

    @endif

</div>

<div class="input-group">

    <label class="input-group-btn">
        <span class="btn btn-primary">
            {{ ucfirst(cms_trans('models.upload.browse')) }}

            {{-- Whether we should keep the old file --}}
            <input name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[keep]" class="file-upload-keep-input" type="hidden" value="1">

            <input id="field-{{ $key }}"
                   type="file"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[upload]"
                   @if ($accept) accept="{{ $accept }}" @endif
                   style="display: none;"
                   @if ($required && ! $translated) required="required" @endif
            >
        </span>
    </label>

    <input type="text" class="form-control" readonly
           @if ($original)
           data-original="{{ $original->originalFilename() }}"
           value="{{ $original->originalFilename() }}"
            @endif
    >

    <label class="input-group-btn">
        <span class="btn btn-danger btn-empty-file-upload" title="{{ cms_trans('models.upload.remove') }}">
            &times;
        </span>
    </label>
</div>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@push('javascript-end')
<!-- form field display strategy: stapler file -->
<script>
    $(function () {

        // Trigger the fileselect event when a new file is selected
        $(document).on('change', ':file', function() {
            var input    = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label    = input.val().replace(/\\/g, '/').replace(/.*\//, '');

            input.trigger('fileselect', [numFiles, label]);
        });

        // Handle the fileselect event to update the placeholder text input and mark the 'keep' hidden input
        $(':file').on('fileselect', function(event, numFiles, label) {
            var inputText = $(this).parents('.input-group').find(':text'),
                inputKeep = $(this).parents('.input-group').find('.file-upload-keep-input'),
                log       = numFiles > 1 ? numFiles + ' files selected' : label;

            if (inputText.length) {
                inputText.val(log);
                inputKeep.val(0);
            } else {
                inputText.val('');
                inputKeep.val(1);
            }
        });

        // Handle button clicks to clear the file input
        $('.btn-empty-file-upload').click(function(event) {
            var fileInput = $(this).parents('.input-group').find(':file'),
                textInput = $(this).parents('.input-group').find(':text'),
                keepInput = $(this).parents('.input-group').find('.file-upload-keep-input');

            fileInput.wrap('<form>').closest('form').get(0).reset();
            fileInput.unwrap();

            textInput.val('');
            keepInput.val(0);

            event.preventDefault();
        });

    })
</script>
@endpush
