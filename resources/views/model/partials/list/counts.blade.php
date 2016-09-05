
<div class="well well-sm">

    @if ($current != $total)
        {{ $current }}
        {{ cms_trans('models.counts.out-of') }}
    @endif

    {{ $total }}

</div>
