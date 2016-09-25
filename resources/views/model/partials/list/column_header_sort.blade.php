
<a class="sort {{ $active ? 'active' : null }}"
   href="?sort={{ $sortKey }}&sortdir={{ $active ? ($direction === 'desc' ? 'asc' : 'desc') : null }}">

    {{ $label }}

    &nbsp;

    @if ($active)
        <i class="fa fa-sort-{{ $direction }}"></i>
    @else
        <i class="fa fa-sort"></i>
    @endif
</a>
