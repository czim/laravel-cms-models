
<div class="input-current-values">
    <table class="table table-striped table-hover records-table pivot-records-table">

        <tbody id="field-{{ $key }}__current__">

            @if ($value)
                @foreach ($value as $valueKey => $valuePosition)

                    @include('cms-models::model.partials.form.strategies.pivot_orderable.pivot_orderable_row', [
                        'rowId'           => "field-{$key}__current-row__{$valueKey}",
                        'hiddenInputName' => ($name ?: $key) . '[' . $valueKey . ']',
                        'key'             => $valueKey,
                        'position'        => $valuePosition ?: 0,
                        'reference'       => array_get($references, $valueKey, $valueKey),
                    ])
                @endforeach
            @endif

        </tbody>
    </table>

    <div style="display: none">
        <table>
            <tbody id="field-{{ $key }}__row-placeholder__" data-input-name="{{ ($name ?: $key) . '[%ID%]' }}">
                @include('cms-models::model.partials.form.strategies.pivot_orderable.pivot_orderable_row', [
                    'rowId'           => "field-{$key}__current-row__%ID%",
                    'hiddenInputName' => '',
                    'key'             => '%ID%',
                    'position'        => '%POSITION%',
                    'reference'       => '%REFERENCE%',
                ])
            </tbody>
        </table>
    </div>
</div>

<div class="input-group">

    <select id="field-{{ $key }}__add-select__" class="form-control select2">
        <option></option>
    </select>

    <label class="input-group-btn">
        <button id="field-{{ $key }}__add-button__"
                class="btn btn-sm btn-primary"
                disabled="disabled"
        >
            {{ cms_trans('common.buttons.add') }}
        </button>
    </label>
</div>


@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@push('javascript-end')
    <!-- form field display strategy: relation plural autocomplete -->
    <script>
        $(function() {

            var addSelect   = $('#field-{{ $key }}__add-select__'),
                addButton   = $('#field-{{ $key }}__add-button__'),
                currentRows = $('#field-{{ $key }}__current__');

            addSelect.select2({
                placeholder : '--',
                allowClear  : {{ $required ? 'false' : 'true' }},
                ajax        : {
                    headers    : {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    initSelection: function(element, callback) {},
                    url        : '{{ cms_route('models-meta.references') }}',
                    type       : 'POST',
                    dataType   : 'json',
                    delay      : 250,

                    data: function (params) {
                        return {
                            model : '{{ str_replace('\\', '\\\\', get_class($record)) }}',
                            type  : 'form.field',
                            key   : '{{ $key }}',
                            search: params.term
                        };
                    },

                    processResults: function (data, params) {

                        // Convert the key/reference pairs from the model meta controller
                        // to the id/text pairs expected by select2.

                        var converted = [];

                        $.each(data, function (key, value) {
                            converted.push({
                                id   : value.key,
                                text : value.reference
                            })
                        });

                        return {
                            results: converted
                        };
                    }
                },

                minimumInputLength: 1
            });

            // The button to add a pivot record to the form is enable/disabled on select updates
            addSelect.on("select2:select", function () {
                addButton.removeAttr('disabled');
            });
            addSelect.on("select2:unselect", function () {
                addButton.attr('disabled', 'disabled');
            });

            // When the add button is clicked, and the selected record is not yet in the form,
            // it is added to the table and the position is set for it
            addButton.click(function (e) {
                e.preventDefault();

                var placeHolder = $('#field-{{ $key }}__row-placeholder__'),
                    html        = placeHolder.html(),
                    selected    = addSelect.select2('data');
                    key         = addSelect.val(),
                    reference   = key,
                    inputName   = placeHolder.attr('data-input-name').replace('%ID%', key);


                // Don't do anything if the connection is already present
                if ($('#field-{{ $key }}__current-row__' + key).length) {
                    return;
                }

                if (null !== selected && selected[0]) {
                    selected = selected[0];
                    reference = selected.text;
                }

                html = html
                        .replace('%ID%', key)
                        .replace('%REFERENCE%', reference);

                currentRows.append(html);

                // Set the hidden input name
                setTimeout(function () {
                    currentRows.find('tr:last-child input[type=hidden]').attr('name', inputName)
                }, 0);

                refreshPositions(currentRows);

                // Empty the add select input
                // Cannot do this: select2 breaks and refuses to load ajax again, gets stuck with old result
                //addSelect.select2().val('').trigger('change');
            });


            // Remove button: removing connections to records
            currentRows.on('click', '.remove-record-action', function () {
                $(this).closest('tr').remove();
                refreshPositions(currentRows);
            });

            // Drag & drop
            currentRows.closest('.pivot-records-table').sortable({
                //handle           : '.orderable-drag-drop',
                containerSelector: 'table',
                itemPath         : '> tbody',
                itemSelector     : 'tr',
                placeholder      : '<tr class="orderable-placeholder"/>',
                bodyClass        : 'orderable-dragging',
                draggedClass     : 'orderable-dragged',
                onDrop           : function ($item, container, _super) {
                    _super($item, container);

                    refreshPositions(currentRows);
                }
            });

            // Refresh index position column values for all orderable pivot rows
            var refreshPositions = function(parent) {

                $(parent).find('tr').each(function(index, row) {
                    $(row).find('input[type=hidden]').val(index + 1);
                });
            };
        });

        $(function () {
            $('.column-orderable [data-toggle="tooltip"]').tooltip({
                delay: {
                    'show': 500,
                    'hide': 50
                }
            })
        });
    </script>
@endpush
