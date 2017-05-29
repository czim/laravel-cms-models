
<form id="scope-form" method="get" action="">
    <input id="scope-input-hidden" type="hidden" name="scope" value="">

    <ul class="nav nav-tabs scope-tabs" role="tablist">

        <li role="presentation" @if ( ! $activeScope) class="active" @endif>
            <a class="scope-tab-activate" href="#" role="tab" data-scope="">
                {{ cms_trans('models.scope.all') }}

                <small class="text-muted">
                    ({{ $totalCount }})
                </small>
            </a>
        </li>

        @foreach ($scopes as $key => $scope)

            <?php
                $count = ($scopeCounts && isset($scopeCounts[ $key ])) ? $scopeCounts[ $key ] : null;
                $class = trim(($activeScope == $scope->method ? 'active' : null) . ' ' . ($count === 0 ? 'disabled' : null));
            ?>

            <li role="presentation" class="{{ $class }}">
                <a class="scope-tab-activate" href="#" role="tab" data-scope="{{ $scope->method }}">
                    {{ ucfirst($scope->display()) }}

                    @if (null !== $count)
                        <small class="text-muted">
                            ({{ $count }})
                        </small>
                    @endif
                </a>
            </li>

        @endforeach
    </ul>
</form>


@cms_script
    <script>
        $('.scope-tab-activate').click(function() {

            if ($(this).parent().hasClass('disabled')) {
                return false;
            }

            $('#scope-input-hidden').val($(this).attr('data-scope'));
            $('#scope-form').submit();
            return false;
        })
    </script>
@cms_endscript

