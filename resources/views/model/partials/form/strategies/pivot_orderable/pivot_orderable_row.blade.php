
<tr id="{{ $rowId }}" class="row-orderable">

    <input type="hidden" name="{{ $hiddenInputName }}" value="{{ $position }}">

    <td class="column column-orderable"
        data-id="{{ $key }}"
        style="width: 2em"
        nowrap="nowrap"
    >
        <div class="btn-group btn-group-xs tr-show-on-hover" role="group">
            <div class="btn btn-default orderable-drag-drop">
                <i class="glyphicon glyphicon-move move"
                   title="{{ cms_trans('models.orderable.drag-to-order') }}"
                   data-toggle="tooltip" data-placement="bottom"></i>
            </div>
        </div>
    </td>

    <td class="column column-reference">
        <span>
            {{ $reference }}
        </span>
    </td>

    <td class="column">
        <div class="btn-group btn-group-xs record-actions pull-right tr-show-on-hover" role="group">

            <a class="btn btn-danger remove-record-action" href="#" role="button"
               title="{{ ucfirst(cms_trans('common.buttons.remove')) }}"
            >
                <i class="glyphicon glyphicon-remove"></i>
                {{ cms_trans('common.buttons.remove') }}
            </a>

        </div>
    </td>

</tr>
