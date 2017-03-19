<?php
namespace Czim\CmsModels\Contracts\ModelInformation;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

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
