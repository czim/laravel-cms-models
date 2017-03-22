<?php
namespace Czim\CmsModels\Test\ModelInformation\Collector;

use Czim\CmsModels\ModelInformation\Collector\ModelInformationFileReader;
use Czim\CmsModels\Test\TestCase;

class ModelInformationFileReaderTest extends TestCase
{

    /**
     * @test
     */
    function it_reads_php_array_data_from_a_path()
    {
        $reader = new ModelInformationFileReader;

        $data = $reader->read($this->getReaderTestPath('phpdata.php'));

        static::assertEquals(['single' => true], $data);
    }

    /**
     * @test
     */
    function it_reads_php_array_data_from_a_path_regardless_of_file_extension()
    {
        $reader = new ModelInformationFileReader;

        $data = $reader->read($this->getReaderTestPath('phpdata.txt'));

        static::assertEquals(['single' => true], $data);
    }
    
    /**
     * @test
     */
    function it_reads_json_data_from_a_path()
    {
        $reader = new ModelInformationFileReader;

        $data = $reader->read($this->getReaderTestPath('jsondata.json'));

        static::assertEquals(['single' => true], $data);
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationFileException
     */
    function it_throws_an_exception_if_file_cannot_be_found()
    {
        $reader = new ModelInformationFileReader;

        $reader->read($this->getReaderTestPath('doesnot.exist'));
    }
    
    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationFileException
     */
    function it_throws_an_exception_if_invalid_php_data_is_found()
    {
        $reader = new ModelInformationFileReader;

        $reader->read($this->getReaderTestPath('brokenphpdata.php'));
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationFileException
     */
    function it_rethrows_an_exception_if_php_data_is_errored()
    {
        $reader = new ModelInformationFileReader;

        $reader->read($this->getReaderTestPath('erroredphpdata.txt'));
    }

    /**
     * @test
     * @expectedException \Czim\CmsModels\Exceptions\ModelConfigurationFileException
     */
    function it_throws_an_exception_if_invalid_json_data_is_found()
    {
        $reader = new ModelInformationFileReader;

        $reader->read($this->getReaderTestPath('brokenjsondata.json'));
    }

    /**
     * @param string $file
     * @return string
     */
    protected function getReaderTestPath($file)
    {
        return realpath(__DIR__ . '/../../Helpers/ModelConfiguration/Reader')
             . '/' . $file;
    }
    
}
