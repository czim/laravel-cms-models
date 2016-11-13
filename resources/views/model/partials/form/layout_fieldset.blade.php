
<fieldset id="fieldset-{{ $key }}">

    <legend>{{ $fieldset->display() }}</legend>

    @foreach ($fieldset->children as $nodeKey => $node)

        @include('cms-models::model.partials.form.layout_node', array_merge(
            compact(
                'node',
                'nodeKey',
                'record',
                'model',
                'values',
                'fields',
                'errors'
            ),
            [
                'parent' => $fieldset,
            ]
        ))

    @endforeach

</fieldset>
