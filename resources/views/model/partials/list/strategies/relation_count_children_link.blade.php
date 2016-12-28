
@if ($link)
    <a href="{{ $link }}">
        <span class="relation-count">
            {{ cms_trans('models.list-parents.children-list-link', [
                'children' => $childrenName,
                'count'    => $count,
            ]) }}
        </span>
    </a>
@else
    <span class="relation-count">
            {{ $count }} {{ $childrenName }}
        </span>
@endif
