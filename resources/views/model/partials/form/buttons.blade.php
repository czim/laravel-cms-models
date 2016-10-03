
<div class="form-group edit-button-row">

    <div class="col-sm-6">

        <button class="btn btn-default edit-button-cancel">
            <span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>
            {{ ucfirst(cms_trans('common.buttons.cancel')) }}
        </button>

    </div>

    <div class="col-sm-6">

        <div class="btn-group pull-right" role="group" aria-label="save">

            <button class="btn btn-primary edit-button-save-and-close">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                {{ ucfirst(cms_trans('common.buttons.save-and-close')) }}
            </button>

            <button type="submit" class="btn btn-primary">
                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                {{ ucfirst(cms_trans('common.buttons.save')) }}
            </button>

        </div>

    </div>
</div>
