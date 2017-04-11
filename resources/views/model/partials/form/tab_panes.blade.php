
<div class="tab-content">

    <?php
        $count = 0;

        $oldTabKey = old(\Czim\CmsModels\Http\Controllers\DefaultModelController::ACTIVE_TAB_PANE_KEY, $activeTab);
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->shouldDisplay())

        <?php
            $count++;

            $tabActive = $oldTabKey ? $oldTabKey === $key : $count == 1;
        ?>

        <div id="tab-{{ $key }}" role="tabpanel" class="tab-pane {{ $tabActive ? 'active' : null }}">

            {{-- Before view --}}
            @if ($tab->before && $tab->before->view)
                @include($tab->before->view, $tab->before->variables())
            @endif

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


            {{-- After view --}}
            @if ($tab->after && $tab->after->view)
                @include($tab->after->view, $tab->after->variables())
            @endif

        </div>

    @endforeach

</div>

