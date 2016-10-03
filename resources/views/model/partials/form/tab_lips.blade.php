

<ul class="nav nav-tabs" role="tablist">

    <?php
        $count = 0;
    ?>

    @foreach ($tabs as $key => $tab)
        @continue( ! $tab->children)

        <?php
            $count++;
        ?>

        <li role="presentation" class="{{ $count == 1 ? 'active' : null }}">
            <a href="#tab-{{ $key }}" aria-controls="tab-{{ $key }}" role="tab" data-toggle="tab">
                {{ $tab->display() }}
            </a>
        </li>

    @endforeach

</ul>
