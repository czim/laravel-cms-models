<?php
namespace Czim\CmsModels\ModelInformation\Collector;

use Czim\CmsModels\Contracts\ModelInformation\Collector\ModelInformationFileReaderInterface;
use Czim\CmsModels\Exceptions\ModelConfigurationFileException;

class ModelInformationFileReader implements ModelInformationFileReaderInterface
{

    /**
     * Attempts to retrieve CMS model information array data from a file.
     *
     * @param string $path
     * @return array
     * @throws ModelConfigurationFileException
     */
    public function read($path)
    {
        $contents = file_get_contents($path);

        // Determine whether to interpret the contents as PHP code or JSON data
        if ('<?php' == substr($contents, 0, 5)) {

            try {
                $contents = eval('?>' . $contents);

            } catch (\Exception $e) {

                throw (new ModelConfigurationFileException(
                    "Could not interpret CMS model configuration PHP data for '{$path}'"
                ))->setPath($path);
            }

            if ( ! is_array($contents)) {
                throw (new ModelConfigurationFileException(
                    "CMS model configuration PHP file did not return an array for '{$path}'"
                ))->setPath($path);
            }

            return $contents;
        }

        // Fall back is to assume the file contains JSON content
        $json = json_decode($contents, true);

        if (null === $json) {
            throw (new ModelConfigurationFileException(
                "Could not interpret CMS model configuration as JSON data for '{$path}'"
            ))->setPath($path);
        }

        return $json;
    }

}
