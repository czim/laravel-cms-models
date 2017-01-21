
<div class="tab-content">

    <?php
        $count = 0;

        $oldTabKey = old(\Czim\CmsModels\Http\Controllers\DefaultModelController::ACTIVE_TAB_PANE_KEY, $activeTab);
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->children)

        <?php
            $count++;

            $tabActive = $oldTabKey ? $oldTabKey === $key : $count == 1;
        ?>

        <div id="tab-{{ $key }}" role="tabpanel" class="tab-pane {{ $tabActive ? 'active' : null }}">

            @foreach ($tab->children as $nodeKey => $node)

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
                        'parent' => $tab,
                    ]
                ))

            @endforeach

        </div>

    @endforeach

</div>

