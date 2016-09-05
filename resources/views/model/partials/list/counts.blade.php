
@if ($current != $total)
    {{ $current }}
    {{ cms_trans('models.counts.out-of') }}
@endif

{{ $total }}

@if ($current == $total)
    {{ cms_trans('models.counts.total') }}
@endif
