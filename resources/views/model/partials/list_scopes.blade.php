
<form id="scope-form" method="get" action="">
    <input id="scope-input-hidden" type="hidden" name="scope" value="">

    <ul class="nav nav-tabs scope-tabs" role="tablist">

        <li role="presentation" @if ( ! $activeScope) class="active" @endif>
            <a class="scope-tab-activate" href="#" role="tab" data-scope="">{{ cms_trans('models.scope.all') }}</a>
        </li>

        @foreach ($scopes as $scope)

            <li role="presentation" @if ($activeScope == $scope->method) class="active" @endif>
                <a class="scope-tab-activate" href="#" role="tab" data-scope="{{ $scope->method }}">{{ ucfirst($scope->display()) }}</a>
            </li>

        @endforeach
    </ul>
</form>


@push('javascript-end')

    <script>
        $('.scope-tab-activate').click(function() {
            $('#scope-input-hidden').val($(this).attr('data-scope'));
            $('#scope-form').submit();
            return false;
        })
    </script>

@endpush
