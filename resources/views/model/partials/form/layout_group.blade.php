
<div id="field-group-{{ $key }}">

    <label for="field-{{ $key }}" class="col-sm-2">
        {{ $group->display() }}
    </label>

    <div class="col-sm-10 field-group-container">

        @foreach ($group->children as $nodeKey => $node)

            <div class="field-group-child">

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
                        'parent' => $group,
                    ]
                ))

            </div>

        @endforeach

    </div>

</div>

