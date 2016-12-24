<script>
    @if ($model->list->activatable)

        @if (cms_auth()->can("{$permissionPrefix}edit"))

            $('.activate-toggle').click(function() {
                var id     = $(this).attr('data-id'),
                    state  = !! parseInt($(this).attr('data-active'), 10),
                    url    = '{{ cms_route("{$routePrefix}.activate", [ 'IDHERE' ]) }}',
                    parent = $(this);

                var data  = {
                    'activate' : ! state
                };

                url = url.replace('IDHERE', id);

                // switch to loading icon
                parent.find('.loading').removeClass('hidden');
                parent.find('.active, .inactive').addClass('hidden');

                $.ajax(url, {
                    'headers': {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    'method'      : 'PUT',
                    'data'        : JSON.stringify(data),
                    'contentType' : 'application/json'
                })
                    .success(function (data) {

                        var active = data.active;

                        if ( ! data.success) {
                            console.log('Failed to update active status...');
                            active = state;
                        }

                        parent.attr('data-active', active ? 1 : 0);

                        if (active) {
                            parent.find('.active').removeClass('hidden');
                            parent.closest('.activate-toggle').addClass('tr-show-on-hover');
                            parent.closest('tr').removeClass('inactive');
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                            parent.closest('.activate-toggle').removeClass('tr-show-on-hover');
                            parent.closest('tr').addClass('inactive');
                        }
                        parent.find('.loading').addClass('hidden');

                    })
                    .error(function (xhr, status, error) {
                        console.log('activate error: ' + error);

                        if (state) {
                            parent.find('.active').removeClass('hidden');
                            parent.closest('.activate-toggle').addClass('tr-show-on-hover');
                            parent.closest('tr').removeClass('inactive');
                        } else {
                            parent.find('.inactive').removeClass('hidden');
                            parent.closest('.activate-toggle').removeClass('tr-show-on-hover');
                            parent.closest('tr').addClass('inactive');
                        }
                        parent.find('.loading').addClass('hidden');
                    });
            });
        @endif

        $(function () {
            $('.column-activate [data-toggle="tooltip"]').tooltip({
                delay: {
                    'show': 250,
                    'hide': 50
                }
            })
        });

    @endif
</script>
