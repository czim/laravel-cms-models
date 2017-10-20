<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Writer;

use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;

interface ModelInformationWriterInterface
{

    /**
     * Writes model information basics to a cms model file.
     *
     * @param ModelInformationInterface $information
     * @param array                     $config
     */
    public function write(ModelInformationInterface $information, array $config = []);

}
