
@if (is_string($node))

    @if (array_key_exists($node, $model->form->fields))

        {{-- If the field should not be displayed on the form, ignore it --}}
        @if (array_key_exists($node, $fields))

            @include('cms-models::model.partials.form.field_strategy', array_merge(
                compact(
                    'record',
                    'model',
                    'values',
                    'errors'
                ),
                [
                    'key'         => $node,
                    'field'       => $model->form->fields[ $node ],
                    'strategy'    => $fieldStrategies[ $node ],
                    'columnWidth' => isset($columnWidth) ? $columnWidth : null,
                ]
            ))

        @endif

    @else

        <span class="text-danger">
            {{ "Error: no field defined for layout key: '{$node}'" }}
        </span>

    @endif

@elseif ($node->type() === \Czim\CmsModels\Support\Enums\LayoutNodeType::FIELDSET)

    @include('cms-models::model.partials.form.layout_fieldset', array_merge(
        compact(
            'record',
            'model',
            'values',
            'fields',
            'errors'
        ),
        [
            'key'      => $nodeKey,
            'fieldset' => $node,
        ]
    ))

@elseif ($node->type() === \Czim\CmsModels\Support\Enums\LayoutNodeType::GROUP)

    @include('cms-models::model.partials.form.layout_group', array_merge(
        compact(
            'record',
            'model',
            'values',
            'fields',
            'errors'
        ),
        [
            'key'   => $nodeKey,
            'group' => $node,
        ]
    ))

@elseif ($node->type() === \Czim\CmsModels\Support\Enums\LayoutNodeType::LABEL)

    @include('cms-models::model.partials.form.layout_label', [
            'key'         => $nodeKey,
            'label'       => $node,
            'columnWidth' => isset($columnWidth) ? $columnWidth : null,
    ])

@else

    <span class="text-danger">
        {{ "Error: unexpected node " . get_class($node) . " for key {$nodeKey}" }}
    </span>

@endif

