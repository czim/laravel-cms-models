<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Collector;

interface ModelInformationFileReaderInterface
{

    /**
     * Attempts to retrieve CMS model information array data from a file.
     *
     * @param string $path
     * @return array
     */
    public function read($path);

}
