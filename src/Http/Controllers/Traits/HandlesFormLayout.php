<?php
namespace Czim\CmsModels\Http\Controllers\Traits;

use Czim\CmsModels\Contracts\Data\ModelFormLayoutNodeInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelInformation;
use UnexpectedValueException;

trait HandlesFormLayout
{

    /**
     * @param bool $creating
     * @return string[]
     */
    protected function getRelevantFormFieldKeys($creating = false)
    {
        $layout = $this->getModelInformation()->form->layout();

        $fieldKeys = [];

        foreach ($layout as $key => $value) {

            if ($value instanceof ModelFormLayoutNodeInterface) {
                $fieldKeys = array_merge($fieldKeys, $this->getNestedFormFieldKeys($value));
                continue;
            }

            if ( ! is_string($value)) {
                continue;
            }

            $fieldKeys[] = $value;
        }

        $fieldKeys = array_unique($fieldKeys);

        // Filter out keys that should not be available
        $fieldKeys = array_filter($fieldKeys, function ($key) use ($creating) {

            if ( ! array_key_exists($key, $this->getModelInformation()->form->fields)) {
                throw new UnexpectedValueException(
                    "Layout field key '{$key}' not found in fields form data for "
                    . $this->getModelInformation()->modelClass()
                );
            }

            if ($creating) {
                return $this->getModelInformation()->form->fields[$key]->create();
            } else {
                return $this->getModelInformation()->form->fields[$key]->update();
            }
        });

        return $fieldKeys;
    }

    /**
     * @param ModelFormLayoutNodeInterface $node
     * @return string[]
     */
    protected function getNestedFormFieldKeys(ModelFormLayoutNodeInterface $node)
    {
        $children = $node->children();

        $fieldKeys = [];

        foreach ($children as $key => $value) {

            if ($value instanceof ModelFormLayoutNodeInterface) {
                $fieldKeys = array_merge($fieldKeys, $this->getNestedFormFieldKeys($value));
                continue;
            }

            if ( ! is_string($value)) {
                continue;
            }

            $fieldKeys[] = $value;
        }

        return $fieldKeys;
    }


    /**
     * @return ModelInformationInterface|ModelInformation
     */
    abstract protected function getModelInformation();

}
