
<ul class="nav nav-tabs" role="tablist">

    <?php
        $count = 0;
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->children)

        <?php
            $count++;

            $oldTabKey = old(\Czim\CmsModels\Http\Controllers\DefaultModelController::ACTIVE_TAB_PANE_KEY);
            $tabActive = $oldTabKey ? $oldTabKey === $key : $count == 1;

            $hasErrors = array_key_exists($key, $errorsPerTab) && $errorsPerTab[$key];
        ?>

        <li role="presentation" class="{{ $tabActive ? 'active' : null }}">
            <a href="#tab-{{ $key }}" aria-controls="tab-{{ $key }}" role="tab"
               data-toggle="tab"
               data-key="{{ $key }}"
               class="edit-form-tab-lip @if ($hasErrors) text-danger @endif"
            >
                {{ $tab->display() }}

                @if ($hasErrors)
                    &nbsp;
                    <span class="glyphicon glyphicon-exclamation-sign text-danger" aria-hidden="true"      title="{{ cms_trans('common.errors.form.errors-on-tab') }}"
                    ></span>
                @endif
            </a>
        </li>

    @endforeach

</ul>
