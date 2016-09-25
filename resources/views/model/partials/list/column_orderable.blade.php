<?php
    $orderable   = $record->{$model->list->orderable};
    $position    = $record->{$model->list->getOrderableColumn()};
?>

<td class="column column-orderable" nowrap="nowrap" data-position="{{ $position }}">
    <div class="btn-group btn-group-xs" role="group">

        @if ($isOrdered)
            <div id="model-orderable-{{ $record->getKey() }}-drag"
                    class="btn btn-default orderable-drag-drop"
                    data-id="{{ $record->getKey() }}">
                <i class="glyphicon glyphicon-move"
                   title="{{ cms_trans('models.orderable.drag-to-order') }}"
                   data-toggle="tooltip" data-placement="bottom"></i>
            </div>
        @endif

        <div class="btn-group btn-group-xs" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">

                @if ($position)
                    {{ $position }}
                @else
                    {{ cms_trans('models.orderable.none') }}
                @endif

                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">

                @if ($position)
                    <li>
                        <a href="#">
                            {{ cms_trans('models.orderable.move-one-up') }}
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            {{ cms_trans('models.orderable.move-one-down') }}
                        </a>
                    </li>
                    <li role="separator" class="divider"></li>
                @endif

                <li>
                    <a href="#">
                        {{ $position ? cms_trans('models.orderable.move-to-top') : cms_trans('models.orderable.add-to-list-at-top') }}
                    </a>
                </li>
                <li>
                    <a href="#">
                        {{ $position ? cms_trans('models.orderable.move-to-bottom') : cms_trans('models.orderable.add-to-list-at-bottom') }}
                    </a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                    <a href="#">
                        {{ $position ? cms_trans('models.orderable.move-to-position') : cms_trans('models.orderable.insert-at-position') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</td>
