
<div @if ( ! is_numeric($key)) id="field-group-{{ $key }}" @endif>

    <label class="col-sm-2 @if ($label->required()) required @endif">
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
                        'fieldStrategies',
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

