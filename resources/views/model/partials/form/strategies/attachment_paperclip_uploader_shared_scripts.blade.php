
@cms_scriptonce
<script>
    /**
     * Deletes an old upload with an AJAX call.
     *
     * @param number uploadId
     */
    var attachmentUploadDelete = function (uploadId, onSuccess, onError) {

        if ( ! onSuccess) {
            onSuccess = function () {};
        }

        if ( ! onError) {
            onError = function () {};
        }

        $.ajax({
            url        : "{{ $uploadDeleteUrl }}".replace('ID_PLACEHOLDER', uploadId),
            headers    : { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type       : 'POST',
            data       : {
                _method: 'DELETE'
            },
            dataType   : 'json',
            success: onSuccess,
            error: onError
        });
    };
</script>
@cms_endscriptonce
