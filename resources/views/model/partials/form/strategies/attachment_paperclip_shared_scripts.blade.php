
@cms_scriptonce
<script>
    /**
     * Triggers fileselect event for file upload field.
     */
    var attachmentUploadTriggerFileSelect = function () {

        var input    = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label    = input.val().replace(/\\/g, '/').replace(/.*\//, '');

        input.trigger('fileselect', [numFiles, label]);
    };
</script>
@cms_endscriptonce
