
@if ($model->list->default_top_relation || $hasActiveListParent)

    <div class="btn-group">

        @if ( ! $hasActiveListParent)
            @if ($topListParentOnly)
                <a href="{{ cms_route("{$routePrefix}.index") }}?parents=all" class="btn btn-default">
                    <i class="fa fa-list-alt"></i> &nbsp;
                    {{ cms_trans('models.list-parents.all-models', [
                        'models' => ucfirst($model->labelPlural())
                    ]) }}
                </a>
            @else
                <a href="{{ cms_route("{$routePrefix}.index") }}?home=1" class="btn btn-default">
                    <i class="fa fa-arrow-up"></i> &nbsp;
                    {{ cms_trans('models.list-parents.top-models', [
                        'models' => ucfirst($model->labelPlural())
                    ]) }}
                </a>
            @endif

        @else

            <a href="{{ cms_route("{$routePrefix}.index") }}?parents=" class="btn btn-default">
                <i class="fa fa-arrow-up"></i> &nbsp;
                @if ($model->list->default_top_relation)
                    {{ cms_trans('models.list-parents.back-to-top-models', [
                        'models' => ucfirst($model->labelPlural())
                    ]) }}
                @else
                    {{ cms_trans('models.list-parents.back-to-all-models', [
                        'models' => ucfirst($model->labelPlural())
                    ]) }}
                @endif
            </a>
        @endif

    </div>
@endif
