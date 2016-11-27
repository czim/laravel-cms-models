
<div class="form-group edit-button-row clearfix">

    <div class="col-sm-4">

        @if ( ! $model->single)
            <a href="{{ route("{$routePrefix}.index") }}" class="btn btn-default edit-button-cancel">
                <span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>
                {{ ucfirst(cms_trans('common.buttons.cancel')) }}
            </a>
        @endif

    </div>

    <div class="col-sm-8">

        <div class="btn-group pull-right" role="group" aria-label="save">

            @if ( ! $model->single)
                <button class="btn btn-success edit-button-save-and-close">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    {{ ucfirst(cms_trans('common.buttons.save-and-close')) }}
                </button>
            @endif

            <button type="submit" class="btn btn-success edit-button-save">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                {{ ucfirst(cms_trans('common.buttons.save')) }}
            </button>

        </div>

    </div>
</div>



@push('javascript-end')

    <script>
        $('form .edit-button-save-and-close').click(function () {
            $('#edit-form-save-and-close-input').val(1);
            $(this).closest('form').submit();
        });
    </script>

@endpush
