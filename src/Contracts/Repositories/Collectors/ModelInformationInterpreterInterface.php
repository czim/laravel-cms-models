<?php
namespace Czim\CmsModels\Contracts\Repositories\Collectors;

use Czim\CmsModels\Contracts\Data\ModelInformationInterface;

interface ModelInformationInterpreterInterface
{

    /**
     * Interprets raw CMS model information as a model information object.
     *
     * @param array|mixed $information
     * @return ModelInformationInterface
     */
    public function interpret($information);

}
