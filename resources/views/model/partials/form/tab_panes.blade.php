
<div class="tab-content">

    <?php
        $count = 0;
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->children)

        <?php
            $count++;
        ?>

        <div id="tab-{{ $key }}" role="tabpanel" class="tab-pane {{ $count == 1 ? 'active' : null }}">

            @foreach ($tab->children as $nodeKey => $node)

                @include('cms-models::model.partials.form.layout_node', array_merge(
                    compact(
                        'node',
                        'nodeKey',
                        'record',
                        'model'
                    ),
                    [
                        'parent' => $tab,
                    ]
                ))

            @endforeach

        </div>

    @endforeach

</div>

