
@if ($sortable)

    <a class="sort {{ $active ? 'active' : null }}"
       href="?sort={{ $sortKey }}&sortdir={{ $active ? ($sortDirection === 'desc' ? 'asc' : 'desc') : null }}">

        {{ $label }}

        &nbsp;

        @if ($active)
            <i class="fa fa-sort-{{ $sortDirection }}"></i>
        @else
            <i class="fa fa-sort"></i>
        @endif
    </a>

@else

    {{ $label }}

@endif


