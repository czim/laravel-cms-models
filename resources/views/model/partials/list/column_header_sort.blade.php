
<a class="sort {{ $active ? 'active' : null }}" href="?sort={{ $key }}&sortdir={{ $active ? ($direction === 'desc' ? 'asc' : 'desc') : null }}">
    {{ ucfirst($column->label) }}

    &nbsp;&nbsp;

    @if ($active)
        <i class="fa fa-sort-{{ $direction }}"></i>
    @else
        <i class="fa fa-sort"></i>
    @endif
</a>
