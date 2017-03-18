<?php
namespace Czim\CmsModels\Analyzer\Processor\Steps;

use Codesleeve\Stapler\Attachment;
use Codesleeve\Stapler\AttachmentConfig;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Czim\CmsModels\Support\Data\Analysis\StaplerAttachment;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Enums\AttributeCast;

class DetectStaplerAttributes extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        // Stapler / attachment attributes
        $attachments = $this->detectStaplerAttachments();

        // Make a list of attributes to insert before the stapler attributes
        /** @var ModelAttributeData[] $inserts */
        $inserts = [];

        foreach ($attachments as $key => $attachment) {

            $attribute = new ModelAttributeData([
                'name'     => $key,
                'cast'     => AttributeCast::STAPLER_ATTACHMENT,
                'type'     => $attachment->image ? 'image' : 'file',
            ]);

            $inserts[ $key . '_file_name' ] = $attribute;
        }


        if ( ! count($inserts)) {
            return;
        }

        $attributes = $this->info->attributes;

        foreach ($inserts as $before => $attribute) {

            $attributes = $this->insertInArray($attributes, $attribute->name, $attribute, $before);
        }

        $this->info->attributes = $attributes;
    }

    /**
     * Returns list of stapler attachments, if the model has any.
     *
     * @return StaplerAttachment[]  assoc, keyed by attribute name
     */
    protected function detectStaplerAttachments()
    {
        $model = $this->model();

        if ( ! ($model instanceof StaplerableInterface)) {
            return [];
        }

        $files = $model->getAttachedFiles();

        $attachments = [];

        /** @var Attachment[] $files */
        foreach ($files as $attribute => $file) {

            /** @var AttachmentConfig $config */
            $config = $file->getConfig();
            $styles = $config->styles;

            $normalizedStyles = [];

            foreach ($styles as $style) {

                if ($style->name === 'original') {
                    continue;
                }

                $normalizedStyles[ $style->name ] = $style->dimensions;
            }

            $attachments[ $attribute ] = new StaplerAttachment([
                'image'   => (is_array($styles) && count($styles) > 1),
                'resizes' => $normalizedStyles,
            ]);
        }

        return $attachments;
    }

    /**
     * Insert an item into an associative array at the position before a given key.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     * @param string $beforeKey
     * @return array
     */
    protected function insertInArray($array, $key, $value, $beforeKey)
    {
        // Find the position of the array
        $position = array_search($beforeKey, array_keys($array));

        // Safeguard: silently append if injected position could not be found
        if (false === $position) {
            // @codeCoverageIgnoreStart
            $array[ $key ] = $value;
            return $array;
            // @codeCoverageIgnoreEnd
        }

        if (0 === $position) {
            return [ $key => $value ] + $array;
        }

        // Slice the array up with the new entry in between
        return array_slice($array, 0, $position, true)
             + [ $key => $value ]
             + array_slice($array, $position, count($array) - $position, true);
    }

}
