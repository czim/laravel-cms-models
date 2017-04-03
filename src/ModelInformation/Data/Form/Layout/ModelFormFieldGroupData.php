<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormFieldGroupData
 *
 * Data container for layout of an (in-row) group of editable fields on a model's edit form.
 *
 * @property string $type
 * @property string $label
 * @property string $label_translated
 * @property string $label_for
 * @property bool   $required
 * @property int[]  $columns
 * @property array|string[] $children
 */
class ModelFormFieldGroupData extends AbstractModelFormLayoutNodeData
{
    const GRID_SIZE_WITHOUT_LABEL = 10;

    protected $attributes = [

        'type' => LayoutNodeType::GROUP,

        // Field label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // The ID of the field label
        'label_for' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // Nested layout (field keys only at this level)
        'children' => [],

        // Grid column widths based on a 12-grid, per item in the group.
        // This is a non-associative array of integers (for col-## grid classes),
        // in the order in which the group's elements appear.
        // The label for the group takes up 2, so the sum of the values must be 10.
        'columns' => [],
    ];

    protected $known = [
        'type',
        'label',
        'label_translated',
        'label_for',
        'required',
        'children',
        'columns',
    ];


    /**
     * Returns grid column layout as list of twelfths.
     *
     * @return int[]
     */
    public function columns()
    {
        $columns = $this->columns ?: [];

        if ( ! count($columns)) {
            $columns = $this->getGroupContentColumns();
        }

        // If columns are set, fill them out if they don't fit the grid
        if ($remainder = $this->getRemainderForColumns($columns)) {
            $columns[ count($columns) - 1 ] += $remainder;
        }

        return $columns;
    }

    /**
     * Returns whether any of the children have field keys that appear in a given list of keys.
     *
     * @param string[] $keys
     * @return bool
     */
    public function matchesFieldKeys(array $keys)
    {
        if ( ! count($keys)) return false;

        return count(array_intersect($keys, $this->descendantFieldKeys())) > 0;
    }

    /**
     * Returns default column widths based on content types.
     *
     * @return int[]
     */
    protected function getGroupContentColumns()
    {
        $contents = $this->getGroupContents();

        $contentCount = count($contents);

        $denominator = floor(static::GRID_SIZE_WITHOUT_LABEL / $contentCount);

        $labelWidth = $denominator > 1 ? 2 : 1;

        // If there is enough space, make labels size 2
        // and spread the rest as evenly as possible over the fields
        // If there is not enough space, try again with labels at size 1
        $labelsTotalWidth = $labelWidth * array_reduce(
            $contents,
            function ($total, $content) {
                return $total + ($content['type'] !== 'field' ? 1 : 0);
            }
        );

        $fieldsCount = array_reduce(
            $contents,
            function ($total, $content) {
                return $total + ($content['type'] === 'field' ? 1 : 0);
            }
        );

        $fieldWidth = (int) floor((static::GRID_SIZE_WITHOUT_LABEL - $labelsTotalWidth) / $fieldsCount);

        $columns = [];

        foreach ($contents as $content) {

            $columns[] = ($content['type'] === 'field') ? $fieldWidth : $labelWidth;
        }

        return $columns;
    }

    /**
     * Returns the groups contents with type information.
     *
     * @return array    list of arrays with key, type child data
     */
    protected function getGroupContents()
    {
        $contents = [];

        foreach ($this->children() as $key => $child) {

            if (is_string($child)) {
                $contents[] = [ 'key' => $child, 'type' => 'field' ];
                continue;
            }

            $contents[] = [ 'key' => $key, 'type' => $child->type() ];
        }

        return $contents;
    }

    /**
     * Returns remainder for column widths, if total is smaller than full grid (minus label).
     *
     * @param int[] $columns
     * @return int
     */
    protected function getRemainderForColumns(array $columns)
    {
        $total = array_reduce($columns, function ($total, $column) { return $total + $column; });

        return max(static::GRID_SIZE_WITHOUT_LABEL - $total, 0);
    }

}
