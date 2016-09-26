
<div id="orderable-position-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form class="orderable-position-modal-form" method="post"
                  data-url="{{ cms_route("{$routePrefix}.position", [ 'IDHERE' ]) }}" action="">
                {{ method_field('put') }}
                {{ csrf_field() }}

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ ucfirst(cms_trans('common.action.close')) }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">
                        {{ ucfirst(cms_trans('models.orderable.move-to-position')) }}
                    </h4>
                </div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="orderable-position-input">{{ cms_trans('models.orderable.position') }}</label>
                        <input type="number" class="form-control text-right" id="orderable-position-input" value="1">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{ ucfirst(cms_trans('common.action.close')) }}
                    </button>
                    <button id="orderable-position-modal-button" class="btn btn-danger">
                        {{ ucfirst(cms_trans('common.action.save')) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
