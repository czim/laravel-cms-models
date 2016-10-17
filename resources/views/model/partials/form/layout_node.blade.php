
@if (is_string($node))

    @if (array_key_exists($node, $model->form->fields))

        @include('cms-models::model.partials.form.field_strategy', array_merge(
            compact(
                'record',
                'model',
                'values',
                'errors'
            ),
            [
                'key'   => $node,
                'field' => $model->form->fields[ $node ],
            ]
        ))

    @else

        <span class="text-danger">
            {{ "Error: no field defined for layout key: '{$node}'" }}
        </span>

    @endif

@elseif ($node->type() === 'fieldset')

    @include('cms-models::model.partials.form.layout_fieldset', array_merge(
        compact(
            'record',
            'model',
            'values',
            'errors'
        ),
        [
            'key'      => $nodeKey,
            'fieldset' => $node,
        ]
    ))

@elseif ($node->type() === 'group')

    @include('cms-models::model.partials.form.layout_group', array_merge(
        compact(
            'record',
            'model',
            'values',
            'errors'
        ),
        [
            'key'   => $nodeKey,
            'group' => $node,
        ]
    ))

@else

    <span class="text-danger">
        {{ "Error: unexpected node " . get_class($node) . " for key {$nodeKey}" }}
    </span>

@endif
