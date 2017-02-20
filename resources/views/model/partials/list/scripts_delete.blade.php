
@if ($model->allowDelete())

    <script>

        /**
         * Performs an AJAX request to check if a model may be deleted.
         *
         * @param id        id of the model
         * @param callback  to be called when ajax responds (takes 1 boolean allowed parameter)
         */
        var isDeletable = function(id, callback) {

            var url = '{{ cms_route("{$routePrefix}.deletable", [ 'IDHERE' ]) }}';

            url = url.replace('IDHERE', id);

            $.ajax(url, {
                'headers': {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {

                    var error = null;
                    if (data.hasOwnProperty('error')) {
                        error = data.error;
                    }

                    callback(data.success, error);
                },
                error: function (xhr, status, error) {
                    console.log('deletable check error: ' + error);
                    callback(false);
                }
            });
        };

        // Button that opens modal
        $('.delete-record-action').click(function () {

            var row       = $(this).closest('tr'),
                form      = $('.delete-modal-form');

            var id        = row.attr('data-id'),
                reference = row.attr('data-reference').trim();

            if ( ! reference || ! reference.length) {
                reference = '#' + id;
            }

            form.attr(
                'action',
                form.attr('data-url').replace('IDHERE', id)
            );

            $('.delete-modal-title').text(
                '{{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->label() }}: ' + reference
            );
        });

        // Check if model is deletable when opening modal
        $('#delete-record-modal').on('show.bs.modal', function (event) {

            var trigger  = $(event.relatedTarget);
            var id       = trigger.closest('tr').attr('data-id');
            var modal    = $(this);

            var button            = modal.find('.delete-modal-button');
            var disallowedMessage = $('#delete-record-modal-disallowed-alert');
            var warningMessage    = modal.find('.undo-warning-alert');

            // Set initial state
            button.removeAttr('disabled');
            disallowedMessage.hide();
            disallowedMessage.empty();
            warningMessage.show();

            // If deletions must be confirmed, handle the controls
            @if ($model->confirmDelete())
                var confirmForm     = modal.find('.confirmation-form');
                var confirmCheckbox = $('#modal-record-delete-confirm');
                confirmForm.show();
                confirmForm.removeClass('has-error');
                confirmCheckbox.removeAttr('checked');
            @endif

            isDeletable(id, function(allowed, error) {

                if ( ! allowed) {
                    button.attr('disabled', 'disabled');
                    confirmForm.hide();
                    warningMessage.hide();
                }

                if (error) {
                    disallowedMessage.show('fast');
                    disallowedMessage.text(error);
                }
            });
        });

        // If deletions must be confirmed, check the confirm checkbox
        @if ($model->confirmDelete())
            $('#delete-record-modal-form').submit(function () {

                if ( ! $('#modal-record-delete-confirm').prop('checked')) {
                    $('#delete-record-modal').find('.confirmation-form')
                        .addClass('has-error')
                        .effect('shake', {
                            'direction': 'up',
                            'distance' : 2.5,
                            'times'    : 2
                        });
                    return false;
                }
            });
        @endif

    </script>

@endif
