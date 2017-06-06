
<div @if ( ! is_numeric($key)) id="field-group-{{ $key }}" @endif
     class="form-group row @if ($group->matchesFieldKeys(array_keys($errors))) has-error @endif"
>

    <label class="col-sm-2 control-label @if ($group->required()) required @endif"
           @if ($group->labelFor()) for="field-{{ $group->labelFor() }}" @endif
    >
        {{ $group->display() }}
    </label>

    @php
        $columnWidths = $group->columns();
        $index = 0;
    @endphp

    @foreach ($group->children as $nodeKey => $node)

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
                'parent'      => $group,
                'columnWidth' => $columnWidths[ $index ],
            ]
        ))

        @php
            $index++
        @endphp
    @endforeach

</div>

