<?php
namespace Czim\CmsModels\Test\Helpers\Models\Stapler;

use Codesleeve\Stapler\ORM\EloquentTrait;

trait EloquentTraitFixed
{
    use EloquentTrait;

    /**
     * @todo Temporary hack to let Stapler work in Laravel 5.5
     *       This should be fixed in Stapler itself.
     *
     * {@inheritdoc}
     */
    protected function originalIsEquivalent($key, $current)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return true;
        }

        return parent::originalIsEquivalent($key, $current);
    }
}
