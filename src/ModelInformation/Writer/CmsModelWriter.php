<?php
namespace Czim\CmsModels\ModelInformation\Writer;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsModels\Contracts\ModelInformation\Data\ModelInformationInterface;
use Czim\CmsModels\Contracts\ModelInformation\Writer\ModelInformationWriterInterface;
use Czim\CmsModels\Exceptions\ModelInformationFileAlreadyExistsException;
use Czim\CmsModels\ModelInformation\Data\ModelInformation;
use File;

class CmsModelWriter implements ModelInformationWriterInterface
{
    const INDENT_SPACE_COUNT = 4;

    const DEFAULT_WRITER_KEYS = [
        'list.columns',
        'form.fields',
    ];

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $information;

    /**
     * Array content to write to file.
     *
     * @var array
     */
    protected $content = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var null|string
     */
    protected $path;

    /**
     * @var null|string
     */
    protected $baseModelNamespace;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    /**
     * Writes model information basics to a cms model file.
     *
     * @param ModelInformationInterface $information
     * @param array                     $config
     * @return string   path to written file
     */
    public function write(ModelInformationInterface $information, array $config = [])
    {
        $this->information = $information;
        $this->config      = $config;

        $this->content = [];
        $this->path    = $this->getInformationTargetPath();
        $keys          = $this->getKeysToWrite();

        $this->checkWhetherFileAlreadyExists();

        foreach ($keys as $key) {
            $this->writeKey($key);
        }

        $this->writeContentToFile();

        return $this->path;
    }

    /**
     * @throws ModelInformationFileAlreadyExistsException
     */
    protected function checkWhetherFileAlreadyExists()
    {
        if (File::exists($this->path)) {
             throw (new ModelInformationFileAlreadyExistsException)
                 ->setModelClass($this->information->modelClass());
        }
    }

    /**
     * @param string $key
     */
    protected function writeKey($key)
    {
        switch ($key) {

            case 'list.columns':
                array_set($this->content, $key, array_keys($this->information->list->columns));
                break;

            case 'form.fields':
                array_set($this->content, $key, array_keys($this->information->form->fields));
                break;

            case 'show.fields':
                array_set($this->content, $key, array_keys($this->information->show->fields));
                break;
        }
    }

    /**
     * Writes the array model information content to file.
     */
    protected function writeContentToFile()
    {
        $content = '<?php' . PHP_EOL . PHP_EOL
                 . 'return ' . $this->getCleanContent() . ';' . PHP_EOL;

        File::put($this->path, $content);
    }

    /**
     * Returns clean writable php array content.
     *
     * @return string
     */
    protected function getCleanContent()
    {
        $content = var_export($this->content, true);

        # Replace the array openers with square brackets
        $content = preg_replace('#^(\s*)array\s*\(#i', '\\1[', $content);
        $content = preg_replace('#=>(\s*)array\s*\(#is', '=> [', $content);

        # Replace the array closers with square brackets
        $content = preg_replace('#^(\s*)\),#im', '\\1],', $content);
        $content = substr($content, 0, -1) . ']';

        // Remove integer indexes for unassociative array lists
        $content = preg_replace('#(\s*)\d+\s*=>\s*(.*)#i', '\\1\\2', $content);

        $content = $this->replaceDoubleSpaceIndentWithCustomSpaceIndent($content);

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function replaceDoubleSpaceIndentWithCustomSpaceIndent($content)
    {
        $lines = explode("\n", $content);

        foreach ($lines as &$line) {
            if (preg_match('#^( +)(.*)$#', $line, $matches)) {
                $line = str_repeat(' ', floor(strlen($matches[1]) / 2) * static::INDENT_SPACE_COUNT)
                      . $matches[2];
            }
        }

        unset($line);

        return implode("\n", $lines);
    }

    /**
     * Returns the path where the models should be stored.
     *
     * @return string
     */
    protected function getInformationTargetPath()
    {
        if ($path = array_get($this->config, 'path')) {
            return $path;
        }

        return rtrim($this->getInformationTargetBasePath(), DIRECTORY_SEPARATOR)
             . DIRECTORY_SEPARATOR
             . $this->getInformationTargetRelativePath();
    }

    /**
     * Returns the base path that should store model relative files.
     *
     * @return string
     */
    protected function getInformationTargetBasePath()
    {
        return config('cms-models.collector.source.dir');
    }

    /**
     * @return string
     */
    protected function getInformationTargetRelativePath()
    {
        $relativeClass = $this->information->modelClass();
        $baseNamespace = $this->getBaseModelsNamespace();

        if (starts_with($relativeClass, $baseNamespace)) {
            $relativeClass = trim(substr($relativeClass, strlen($baseNamespace)), '\\');
        }

        return str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    }

    /**
     * Returns the base namespace string which is the models root.
     *
     * @return string
     */
    protected function getBaseModelsNamespace()
    {
        return config('cms-models.collector.source.models-namespace');
    }

    /**
     * Returns the keys that should be written.
     *
     * @return string[]
     */
    protected function getKeysToWrite()
    {
        $keys = array_get($this->config, 'keys', ['*']);

        if (in_array('*', $keys)) {
            return $this->getWritableDefaultKeys();
        }

        return $keys;
    }

    /**
     * @return string[]
     */
    protected function getWritableDefaultKeys()
    {
        return config('cms-models.writer.keys', static::DEFAULT_WRITER_KEYS);
    }

}
