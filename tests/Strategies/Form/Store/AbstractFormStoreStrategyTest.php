<?php
namespace Czim\CmsModels\Test\Strategies\Form\Store;

use Czim\CmsModels\Contracts\Support\Validation\ValidationRuleMergerInterface;
use Czim\CmsModels\Support\Validation\ValidationRuleMerger;
use Czim\CmsModels\Test\TestCase;

abstract class AbstractFormStoreStrategyTest extends TestCase
{

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->app->bind(ValidationRuleMergerInterface::class, ValidationRuleMerger::class);
    }

}
