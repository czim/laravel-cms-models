
<div id="field-group-{{ $key }}">

    @foreach ($group->children as $nodeKey => $node)

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
                'parent' => $group,
            ]
        ))

    @endforeach

</div>
