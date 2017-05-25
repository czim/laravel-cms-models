
<div id="field-{{ $key }}-preview_original" class="form-control-static preview-state-server">

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

<div id="field-{{ $key }}-preview_ajax" class="form-control-static preview-state-ajax" style="display: none">

    <div class="state-empty" style="display: none">
        <span class="text-muted">
            <em>{{ ucfirst(cms_trans('models.upload.nothing-uploaded')) }}</em>
        </span>
    </div>

    <div class="state-preview" style="display: none">
        <span class="text-primary preview-state-filename" style="margin-right: 2em">
            filename
        </span>

        <span class="text-muted  preview-state-type-and-size">
            content-type,
            size bytes
        </span>
    </div>

    <div class="state-progress" style="display: none">
        <div class="progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">
                <span class="sr-only">0%</span>
            </div>
        </div>
    </div>

    <div class="state-error" style="display: none">

        <div class="alert alert-danger" role="alert" style="margin-bottom: 0; padding: 10px">
            <i class="glyphicon glyphicon-exclamation-sign" style="padding-right: 0.5em"></i>
            <span class="message">Error</span>
            <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

</div>

<div id="field-{{ $key }}-input_group" class="input-group">

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

            <input id="field-{{ $key }}-upload_id"
                   class="file-upload-id-input"
                   type="hidden"
                   name="{{ $name ?: (isset($baseKey) ? $baseKey : $key) }}[upload_id]"
                   style="display: none;"
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


@push('javascript-end')
<!-- form field display strategy: stapler file -->
<script>
    $(function () {

        // Trigger the fileselect event when a new file is selected
        $(document).on('change', "#field-{{ $key }}:file", function() {
            var input    = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label    = input.val().replace(/\\/g, '/').replace(/.*\//, '');

            input.trigger('fileselect', [numFiles, label]);
        });

        // Handle the fileselect event to update the placeholder text input and mark the 'keep' hidden input
        $(document).on('fileselect', "#field-{{ $key }}:file", function(event, numFiles, label) {
            var inputText = $(this).parents('.input-group').find(':text'),
                inputKeep = $(this).parents('.input-group').find('.file-upload-keep-input'),
                log       = numFiles > 1 ? numFiles + ' files selected' : label;

            if (inputText.length) {
                inputText.val(log);
                inputKeep.val(0);

                // Send the file with Ajax
                var file   = document.getElementById("field-{{ $key }}").files[0];
                var reader = new FileReader();

                reader.readAsText(file, 'UTF-8');
                reader.onload = function () {
                    var fileName = document.getElementById("field-{{ $key }}").files[0].name;

                    var data = new FormData();
                    data.append('file', document.getElementById("field-{{ $key }}").files[0]);
                    data.append('name', fileName);
                    data.append('reference', "{{ str_replace('\\', '\\\\', get_class($record)) }}::field-{{ $key }}");
                    data.append('validation', "{!! str_replace('"', '\\"', str_replace('\\', '\\\\', json_encode($uploadValidation))) !!}");

                    // Replace server preview with ajax preview
                    $("#field-{{ $key }}-preview_original").hide();
                    $("#field-{{ $key }}-preview_ajax").show();

                    // Start loading state
                    $("#field-{{ $key }}-input_group").find("#field-{{ $key }}, input[type=text], .btn-empty-file-upload").prop('disabled', true);
                    $("#field-{{ $key }}-preview_ajax > div").hide();
                    $("#field-{{ $key }}-preview_ajax .state-progress .progress-bar")
                        .prop('aria-valuenow', 0).css('width', '0%');
                    $("#field-{{ $key }}-preview_original").hide();
                    $("#field-{{ $key }}-preview_ajax .state-progress").show();

                    var options = {
                        url        : '{{ $uploadUrl }}',
                        type       : 'POST',
                        data       : data,
                        cache      : false,
                        processData: false,
                        contentType: false,
                        xhr: function(){
                            var xhr = $.ajaxSettings.xhr();
                            if (xhr.upload) {
                                xhr.upload.addEventListener('progress', function(event) {
                                    var percent  = 0,
                                        position = event.loaded || event.position,
                                        total    = event.total;

                                    if (event.lengthComputable) {
                                        percent = Math.ceil(position / total * 100);
                                    }

                                    $("#field-{{ $key }}-preview_ajax .state-progress .progress-bar")
                                        .prop('aria-valuenow', percent).css('width', percent + '%');
                                }, true);
                            }
                            return xhr;
                        },
                        success: function(data) {
                            $("#field-{{ $key }}-preview_ajax > div").hide();

                            if (data.success) {
                                $("#field-{{ $key }}-preview_ajax .state-preview").show();
                                $("#field-{{ $key }}-upload_id").val(data.id);
                                $("#field-{{ $key }}").val('');

                                var i           = Math.floor( Math.log(data.size) / Math.log(1024) );
                                var typeAndSize = data.mimetype + ', ' + (( data.size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB'][i]);

                                $("#field-{{ $key }}-preview_ajax .state-preview .preview-state-filename").text(data.name);
                                $("#field-{{ $key }}-preview_ajax .state-preview .preview-state-type-and-size").text(typeAndSize);

                            } else {
                                // Server reported (generic) error
                                $("#field-{{ $key }}-preview_ajax .state-error .message").text('Upload failed.');
                                $("#field-{{ $key }}-preview_ajax .state-error .message").text(
                                    data.hasOwnProperty('error') ? data.error : 'Upload failed.'
                                );
                                $("#field-{{ $key }}-preview_ajax .state-error").show();
                                $("#field-{{ $key }}-upload_id").val('');
                                inputText.val('');
                            }

                            // Stop loading state
                            $("#field-{{ $key }}-input_group").find("#field-{{ $key }}, input[type=text], .btn-empty-file-upload").prop('disabled', false);
                        },
                        error: function(jqXHR, textStatus) {
                            // Handle errors here
                            $("#field-{{ $key }}-preview_ajax > div").hide();
                            $("#field-{{ $key }}-preview_ajax .state-error .message").text(textStatus);
                            $("#field-{{ $key }}-preview_ajax .state-error").show();

                            // Stop loading state
                            $("#field-{{ $key }}-input_group").find("#field-{{ $key }}, input[type=text], .btn-empty-file-upload").prop('disabled', false);
                        }
                    };

                    // Make sure no text encoding stuff is done by xhr for old browsers
                    if (data.fake) {
                        options.xhr = function() {
                            var xhr  = $.ajaxSettings.xhr();
                            xhr.send = xhr.sendAsBinary; return xhr;
                        };
                        options.contentType = "multipart/form-data; boundary=" + data.boundary;
                        options.data        = data.toString();
                    }

                    $.ajax(options);
                };

            } else {
                inputText.val('');
                inputKeep.val(1);
            }
        });

        // On error, allow closing the error and showing the server state preview
        $(document).on('click', "#field-{{ $key }}-preview_ajax .state-error button.close", function () {
            $("#field-{{ $key }}-preview_ajax").hide();
            $("#field-{{ $key }}-preview_original").show();
        });


        // Handle button clicks to clear the file input
        $(document).on('click', "#field-{{ $key }}-input_group .btn-empty-file-upload", function(event) {
            var fileInput = $(this).parents('.input-group').find(':file'),
                textInput = $(this).parents('.input-group').find(':text'),
                keepInput = $(this).parents('.input-group').find('.file-upload-keep-input'),
                idInput   = $(this).parents('.input-group').find('.file-upload-id-input'),
                uploadId  = idInput.val();

            fileInput.wrap('<form>').closest('form').get(0).reset();
            fileInput.unwrap();

            textInput.val('');
            keepInput.val(0);
            idInput.val('');

            $("#field-{{ $key }}-preview_ajax").hide();
            $("#field-{{ $key }}-preview_original").show();

            // If the file upload id is set, the uploaded file should be cleaned up
            if (uploadId) {
                $.ajax({
                    url        : "{{ $uploadDeleteUrl }}".replace('ID_PLACEHOLDER', uploadId),
                    headers    : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    type       : 'POST',
                    data       : {
                        _method: 'DELETE'
                    },
                    dataType   : 'json',
                    success: function() {
                        $("#field-{{ $key }}-upload_id").val('');
                    },
                    error: function() {
                        $("#field-{{ $key }}-upload_id").val('');
                    }
                });
            }

            event.preventDefault();
        });

    })
</script>
@endpush
