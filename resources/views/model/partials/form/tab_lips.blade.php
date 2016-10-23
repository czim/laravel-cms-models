

<ul class="nav nav-tabs" role="tablist">

    <?php
        $count = 0;
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->children)

        <?php
            $count++;

            $hasErrors = array_key_exists($key, $errorsPerTab) && $errorsPerTab[$key];
        ?>

        <li role="presentation" class="{{ $count == 1 ? 'active' : null }}">
            <a href="#tab-{{ $key }}" aria-controls="tab-{{ $key }}" role="tab" data-toggle="tab"
               @if ($hasErrors)
                   class="text-danger"
               @endif
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
