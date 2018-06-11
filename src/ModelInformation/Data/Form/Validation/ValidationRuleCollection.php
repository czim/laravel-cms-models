<?php
namespace Czim\CmsModels\ModelInformation\Data\Form\Validation;

use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleCollectionInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation\ValidationRuleDataInterface;
use Illuminate\Support\Collection;

/**
 * Class ValidationRuleCollection
 *
 * Collection of rules for multiple different form field keys.
 */
class ValidationRuleCollection extends Collection implements ValidationRuleCollectionInterface
{

    /**
     * @return array
     */
    public function toArray()
    {
        // The collection may contain separate items that share a field dot-notation key.
        // These should be combined into one array, keyed by the shared dot-notation key.
        $grouped = $this->groupBy(function (ValidationRuleDataInterface $item) {
            return $item->key();
        });

        $array = [];

        // Since the items in this collection are always ValidationRuleDataInterface
        // instances, we can toArray them and merge the results.
        foreach ($grouped as $key => $group) {
            /** @var Collection|ValidationRuleDataInterface[] $group */

            $array[ $key ] = [];

            foreach ($group as $rules) {
                $array[ $key ] = array_merge($array[ $key ], $rules->rules());
            }
        }

        return $array;
    }

}
