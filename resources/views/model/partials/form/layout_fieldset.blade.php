
<fieldset id="fieldset-{{ $key }}">

    @foreach ($fieldset->children as $nodeKey => $node)

        @include('cms-models::model.partials.form.layout_node', array_merge(
            compact(
                'node',
                'nodeKey',
                'record',
                'model',
                'values',
                'errors'
            ),
            [
                'parent' => $fieldset,
            ]
        ))

    @endforeach

</fieldset>
