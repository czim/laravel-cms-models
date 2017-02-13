
<div id="delete-record-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ ucfirst(cms_trans('common.action.close')) }}">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title delete-modal-title">
                    {{ ucfirst(cms_trans('common.action.delete')) }} {{ $model->label() }}
                </h4>
            </div>

            <div class="modal-body">

                <div class="alert alert-danger undo-warning-alert" role="alert">
                    <i class="fa fa-exclamation-triangle"></i>
                    &nbsp;
                    {{ cms_trans('common.cannot-undo') }}
                </div>

                @if ($model->confirmDelete())
                    <div class="form-group confirmation-form has-error">
                        <div class="checkbox checkbox-danger">
                            <label for="modal-record-delete-confirm" class="control-label">
                                <input id="modal-record-delete-confirm" class="modal-delete-confirm" name="confirm_delete" type="checkbox" value="on">
                                {{ ucfirst(cms_trans('common.action.confirm')) }}
                            </label>
                        </div>
                    </div>
                @endif

                <div id="delete-record-modal-disallowed-alert" class="alert alert-danger" role="alert" style="display: none"></div>
            </div>

            <div class="modal-footer">
                <form id="delete-record-modal-form" class="delete-modal-form" method="post" data-url="{{ cms_route("{$routePrefix}.destroy", [ 'IDHERE' ]) }}" action="">
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

