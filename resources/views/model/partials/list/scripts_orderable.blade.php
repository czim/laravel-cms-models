@if ($model->list->orderable)
    <script>

        /**
         * Opens the modal to set a specific orderable position.
         *
         * @param parent    the column-orderable data parent
         */
        var openOrderableModal = function(parent) {

            var form = $('form.orderable-position-modal-form');
            form.attr(
                'action',
                form.attr('data-url').replace('IDHERE', parent.attr('data-id'))
            );

            var input = $('#orderable-position-input');

            // set the current value on the input field
            input.val(parent.attr('data-position'));

            // store the index of the table row on the modal
            $('#orderable-position-modal').data('data-tr-index', parent.closest('tr').index());
        };

        $('#orderable-position-modal').on('shown.bs.modal', function () {
            $('#orderable-position-input').focus().select();
        });

        /**
         * Performs AJAX request to set the orderable position for a model.
         *
         * @param parent    the column-orderable data parent
         * @param position  position integer or enum string
         */
        var setOrderablePosition = function(parent, position) {

            var url  = '{{ cms_route("{$routePrefix}.position", [ 'IDHERE' ]) }}',
                id   = parent.attr('data-id'),
                data = {'position': position};

            url = url.replace('IDHERE', id);

            // loading state
            parent.find('.orderable-drag-drop .move').addClass('hidden');
            parent.find('.orderable-drag-drop .loading').removeClass('hidden');

            $.ajax(url, {
                'headers'    : {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                'method'     : 'PUT',
                'data'       : JSON.stringify(data),
                'contentType': 'application/json'
            })
                    .success(function (data) {
                        var position = data.position;

                        if (!data.success) {
                            console.log('Failed to update orderable position...');
                            position = null;
                        }

                        location.reload();
                    })
                    .error(function (xhr, status, error) {
                        console.log('orderable position error: ' + error);
                        parent.find('.orderable-drag-drop .loading').addClass('hidden');
                        parent.find('.orderable-drag-drop .move').removeClass('hidden');
                    });
        };

        $('.orderable-action-up').click(function (event) {
            event.preventDefault();
            setOrderablePosition($(this).closest('.column-orderable'), 'up');
        });

        $('.orderable-action-down').click(function (event) {
            event.preventDefault();
            setOrderablePosition($(this).closest('.column-orderable'), 'down');
        });

        $('.orderable-action-top').click(function (event) {
            event.preventDefault();
            setOrderablePosition($(this).closest('.column-orderable'), 'top');
        });

        $('.orderable-action-bottom').click(function (event) {
            event.preventDefault();
            setOrderablePosition($(this).closest('.column-orderable'), 'bottom');
        });

        $('.orderable-action-remove').click(function (event) {
            event.preventDefault();
            setOrderablePosition($(this).closest('.column-orderable'), 'remove');
        });

        $('.orderable-action-position').click(function (event) {
            event.preventDefault();
            openOrderableModal($(this).closest('.column-orderable'));
        });

        $('#orderable-position-modal-button').click(function (event) {
            event.preventDefault();

            // get column-orderable parent for row index
            var index  = $('#orderable-position-modal').data('data-tr-index');
            var parent = $('tr.records-row').eq(index).find('.column-orderable');

            setOrderablePosition(parent, $('#orderable-position-input').val());
        });


        {{-- Orderable: drag and drop --}}
        @if ($model->list->getOrderableColumn() === $sortColumn)
            $(function () {
                $('.records-table').sortable({
                    handle           : '.orderable-drag-drop',
                    containerSelector: 'table',
                    itemPath         : '> tbody',
                    itemSelector     : 'tr',
                    placeholder      : '<tr class="orderable-placeholder"/>',
                    bodyClass        : 'orderable-dragging',
                    draggedClass     : 'orderable-dragged',
                    onDrop           : function ($item, container, _super) {
                        _super($item, container);

                        var oldPosition = parseInt($($item).find('.column-orderable').attr('data-position'), 10),
                            newPosition,
                            newInList,
                            relative,
                            reversed = {{ $sortDirection == 'desc' ? 'true' : 'false' }};


                        // determine the new position to set by the surrounding items: base position is the top of the
                        // two positions between which the item should end up
                        if ($item.index() > 0) {
                            relative    = $($item).prev().find('.column-orderable');
                            newPosition = parseInt(relative.attr('data-position'), 10) - (reversed ? 1 : 0);
                        } else {
                            relative    = $($item).next().find('.column-orderable');
                            newPosition = parseInt(relative.attr('data-position'), 10) - (reversed ? 0 : 1);
                        }

                        // check whether the new position is now taken by a record in the list
                        newInList = parseInt(relative.attr('data-in-list'), 10) > 0;

                        if ( ! newInList) {
                            // if we're dragging the item out of the list, move it to the bottom for now
                            newPosition = 'bottom';
                        } else {
                            // return if the position is unchanged
                            if (oldPosition == newPosition || oldPosition == newPosition + 1) {
                                return;
                            }

                            // adjust if we're dragging an item up
                            if (oldPosition > newPosition) {
                                newPosition += 1;
                            }
                        }

                        // initiate ajax call
                        setOrderablePosition($($item).find('.column-orderable'), newPosition);
                    }
                });
            });

            $(function () {
                $('.column-orderable [data-toggle="tooltip"]').tooltip({
                    delay: {
                        'show': 500,
                        'hide': 50
                    }
                })
            });
        @endif
    </script>
@endif

