<?php
namespace Czim\CmsModels\Strategies\Action;

use Czim\CmsModels\Contracts\Data\ModelActionReferenceDataInterface;
use Czim\CmsModels\Contracts\Strategies\ActionStrategyInterface;
use Czim\CmsModels\Support\Data\ModelActionReferenceData;

abstract class AbstractActionStrategy implements ActionStrategyInterface
{

    /**
     * @var ModelActionReferenceDataInterface|ModelActionReferenceData
     */
    protected $actionData;

    /**
     * @var string
     */
    protected $modelClass;


    /**
     * Initializes the strategy instances for further calls.
     *
     * @param ModelActionReferenceDataInterface $data
     * @param string                            $modelClass FQN of current model
     * @return $this
     */
    public function initialize(ModelActionReferenceDataInterface $data, $modelClass)
    {
        $this->actionData = $data;
        $this->modelClass = $modelClass;

        $this->performInit();

        return $this;
    }

    /**
     * Performs initialization.
     * Override this to customize strategy implementations.
     */
    protected function performInit()
    {
    }

}
