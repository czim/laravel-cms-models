<?php
namespace Czim\CmsModels\Strategies\ListColumn;

use Czim\Paperclip\Contracts\AttachmentInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;

class PaperclipImage extends AbstractListDisplayStrategy
{
    const VIEW = 'cms-models::model.partials.list.strategies.paperclip_image';

    const WIDTH  = 64;
    const HEIGHT = 64;

    /**
     * The keys/aliases for variant strategy steps that may contain resize dimensions.
     *
     * @var string[]
     */
    protected $resizeVariantStepKeys = [
        'resize',
    ];


    /**
     * Renders a display value to print to the list view.
     *
     * @param Model $model
     * @param mixed|AttachmentInterface $source     source column, method name or value
     * @return string|View
     */
    public function render(Model $model, $source)
    {
        $source = $this->resolveModelSource($model, $source);

        if ( ! ($source instanceof AttachmentInterface)) {
            throw new UnexpectedValueException("Paperclip strategy expects Attachment as source");
        }

        $resize = $this->getResizetoUse($source);

        if ($this->listColumnData) {
            $width  = array_get($this->listColumnData->options, 'width');
            $height = array_get($this->listColumnData->options, 'height');
        } else {
            $width  = null;
            $height = null;
        }

        return view(static::VIEW, [
            'exists'      => $source->size() > 0,
            'filename'    => $source->originalFilename(),
            'urlThumb'    => $source->url($resize),
            'urlOriginal' => $source->url(),
            'width'       => $width ?: $height ?: static::WIDTH,
            'height'      => $height ?: $width ?: static::HEIGHT,
        ]);
    }

    /**
     * Returns the stapler resize to display.
     *
     * @param AttachmentInterface $attachment
     * @return string
     */
    protected function getResizetoUse(AttachmentInterface $attachment)
    {
        return array_get(
            $this->options(),
            'variant',
            $this->getSmallestResize($attachment)
        );
    }

    /**
     * Returns smallest available resize for the attachment.
     *
     * @param AttachmentInterface $attachment
     * @return null|string
     */
    protected function getSmallestResize(AttachmentInterface $attachment)
    {
        $smallestKey = null;
        $smallest    = null;

        foreach (array_get($attachment->getNormalizedConfig(), 'variants', []) as $variantKey => $variantSteps) {

            if ( ! $variantSteps) {
                continue;
            }

            foreach ($variantSteps as $stepKey => $step) {

                if ( ! in_array($stepKey, $this->resizeVariantStepKeys)) {
                    continue;
                }

                if ( ! preg_match('#(\d+)?x(\d+)?#', array_get($step, 'dimensions', ''), $matches)) {
                    continue;
                }

                $smallestForVariant = null;

                if ((int) $matches[1] > 0) {
                    $smallestForVariant = (int) $matches[1];
                }

                if ((int) $matches[2] > 0 && (int) $matches[2] < $smallestForVariant) {
                    $smallestForVariant = (int) $matches[2];
                }

                if (    null !== $smallestForVariant
                    &&  ($smallestForVariant < $smallest || null === $smallest)
                ) {
                    $smallestKey = $variantKey;
                    $smallest    = $smallestForVariant;
                }

                break;
            }
        }

        return $smallestKey;
    }

}
