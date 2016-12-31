
<div class="btn-group">
    <a href="{{ cms_route("{$routePrefix}.index") }}?parents=" class="btn btn-default">
        <i class="fa fa-arrow-up"></i> &nbsp;
        @if ($model->list->default_top_relation)
            {{ cms_trans('models.list-parents.back-to-top-models', [
                'models' => ucfirst($model->verbose_name_plural)
            ]) }}
        @else
            {{ cms_trans('models.list-parents.back-to-all-models', [
                'models' => ucfirst($model->verbose_name_plural)
            ]) }}
        @endif
    </a>
</div>
