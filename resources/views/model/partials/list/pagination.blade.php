
@if ($records->lastPage() > 1)

    <div class="listing-pagination">
        {{ $records->links() }}
    </div>

@endif

@if ($pageSizeOptions && count($pageSizeOptions) > 1)

    <div class="pagination-part-container pagination-pagesize-container">
        <form id="form-pagination-page-size" class="form-inline" role="form" method="get">

            <label for="input-pagination-page-size">
                {{ ucfirst(cms_trans('models.pagination.page-size')) }}:
                &nbsp;
            </label>

            <select id="input-pagination-page-size" class="form-control" name="pagesize">

                @foreach ($pageSizeOptions as $size)
                    <option value="{{ $size }}" {{ $pageSize == $size ? 'selected="selected"' : null }}>
                        {{ $size }}
                    </option>
                @endforeach

            </select>
        </form>
    </div>

@endif



@cms_script
    <script>
        $(function() {
            $('#input-pagination-page-size').change(function() {
                $('#form-pagination-page-size').submit();
            });
        })
    </script>
@cms_endscript
