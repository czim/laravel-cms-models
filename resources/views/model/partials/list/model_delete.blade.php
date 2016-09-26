<div id="delete-record-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ ucfirst(cms_trans('common.action.close')) }}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title delete-modal-title">
                    {{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->verbose_name }}
                </h4>
            </div>
            <div class="modal-body">
                <p class="text-danger">{{ cms_trans('common.cannot-undo') }}</p>
            </div>
            <div class="modal-footer">
                <form class="delete-modal-form" method="post" data-url="{{ cms_route("{$routePrefix}.destroy", [ 'IDHERE' ]) }}" action="">
                    {{ method_field('delete') }}
                    {{ csrf_field() }}

                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{ ucfirst(cms_trans('common.action.close')) }}
                    </button>
                    <button type="submit" class="btn btn-danger delete-modal-button">
                        {{ ucfirst(cms_trans('common.action.delete')) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
