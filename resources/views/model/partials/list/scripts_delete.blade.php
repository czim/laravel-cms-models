
@if ($model->allowDelete())

    <script>
        $('.delete-record-action').click(function () {
            var form = $('.delete-modal-form');
            form.attr(
                    'action',
                    form.attr('data-url').replace('IDHERE', $(this).attr('data-id'))
            );
            $('.delete-modal-title').text(
                    '{{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->verbose_name }} #' +
                    $(this).attr('data-id')
            );
        });
    </script>

@endif
