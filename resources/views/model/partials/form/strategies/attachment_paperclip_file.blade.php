

<div class="form-control-static">

    @if (   ! ($original instanceof \Czim\Paperclip\Contracts\AttachmentInterface)
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
            <input name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[keep]" class="file-upload-keep-input" type="hidden" value="{{ $record->exists ? 1 : null }}">

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

    @if ( ! $required || $translated)
        <label class="input-group-btn">
            <span class="btn btn-danger btn-empty-file-upload" title="{{ cms_trans('models.upload.remove') }}">
                &times;
            </span>
        </label>
    @endif
</div>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@cms_script
<!-- form field display strategy: paperclip file -->
<script>
    $(function () {

        // Trigger the fileselect event when a new file is selected
        $(document).on('change', ':file', attachmentUploadTriggerFileSelect);

        // Handle the fileselect event to update the placeholder text input and mark the 'keep' hidden input
        $(document).on('fileselect', ':file', function(event, numFiles, label) {
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
        $(document).on('click', '.btn-empty-file-upload', function(event) {
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
@cms_endscript

@include('cms-models::model.partials.form.strategies.attachment_paperclip_shared_scripts')
