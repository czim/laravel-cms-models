<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Layout;

use Czim\CmsModels\Contracts\Data\ModelFormTabDataInterface;
use Czim\CmsModels\ModelInformation\Data\ModelViewReferenceData;
use Czim\CmsModels\Support\Enums\LayoutNodeType;

/**
 * Class ModelFormTabData
 *
 * Data container that describes a tab pane on a model's create/update form page
 *
 * @property string                 $type
 * @property string                 $label
 * @property string                 $label_translated
 * @property bool                   $required
 * @property array                  $children
 * @property ModelViewReferenceData $before
 * @property ModelViewReferenceData $after
 */
class ModelFormTabData extends AbstractModelFormLayoutNodeData implements ModelFormTabDataInterface
{

    protected $objects = [
        'before' => ModelViewReferenceData::class,
        'after'  => ModelViewReferenceData::class,
    ];

    protected $attributes = [

        'type' => LayoutNodeType::TAB,

        // Tab label (or translation key) to show
        'label'            => null,
        'label_translated' => null,

        // Whether the fields belonging to this are required (affects display only)
        'required' => null,

        // Nested layout children (field keys or fieldsets/groups)
        'children' => [],

        // Views to show before and/or after the form field. Instance of ModelViewReferenceData.
        'before' => null,
        'after'  => null,
    ];

    protected $known = [
        'type',
        'label',
        'label_translated',
        'required',
        'children',
        'before',
        'after',
    ];


    /**
     * @param ModelFormTabDataInterface|ModelFormTabData $with
     */
    public function merge(ModelFormTabDataInterface $with)
    {
        foreach ($this->getKeys() as $key) {
            $this->mergeAttribute($key, $with->{$key});
        }
    }

}
