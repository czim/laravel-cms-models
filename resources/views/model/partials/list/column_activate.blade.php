@php
    // todo: make strategy dependent...
    $active = $record->{$model->list->active_column};
@endphp

<td class="column column-activate">

    @if (cms_auth()->can("{$permissionPrefix}edit"))

        <div id="model-activate-{{ $record->getKey() }}"
             class="activate-toggle {{ $active ? 'tr-show-on-hover' : null }}"
             data-id="{{ $record->getKey() }}"
             data-active="{{ $active ? 1 : 0 }}">

            <div class="icon-wrapper">
                <i class="glyphicon glyphicon-ban-circle text-danger inactive {{ $active ? 'hidden' : null }}"
                   title="{{ ucfirst(cms_trans('models.activatable.deactivated')) }}"
                   data-toggle="tooltip" data-placement="right"></i>
                <i class="glyphicon glyphicon-ok-sign text-success active {{ ! $active ? 'hidden' : null }}"
                   title="{{ ucfirst(cms_trans('models.activatable.deactivate')) }}"></i>
                <i class="glyphicon glyphicon-refresh text-muted loading gly-spin hidden"
                   title="{{ ucfirst(cms_trans('models.activatable.activate')) }}"></i>
            </div>
        </div>

    @else

        <div class="activate-toggle">

            <div class="icon-wrapper">
                @if ($active)
                    <i class="glyphicon glyphicon-ok-sign text-success active"
                       title="{{ ucfirst(cms_trans('models.activatable.activated')) }}"
                       data-toggle="tooltip" data-placement="right"></i>
                @else
                    <i class="glyphicon glyphicon-ban-circle text-danger inactive"
                       title="{{ ucfirst(cms_trans('models.activatable.deactivated')) }}"
                       data-toggle="tooltip" data-placement="right"></i>
                @endif
            </div>
        </div>
    @endif
</td>
