<?php
namespace Czim\CmsModels\Analyzer\Processor\Steps;

use Czim\CmsModels\Contracts\Analyzer\AnalyzerStepInterface;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Analyzer\ModelAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LimitIterator;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use SplFileObject;

abstract class AbstractAnalyzerStep implements AnalyzerStepInterface
{

    /**
     * @var ModelAnalyzerInterface
     */
    protected $analyzer;

    /**
     * @var ModelInformationInterface|ModelInformation
     */
    protected $info;

    /**
     * Sets the parent model analyzer processor.
     *
     * @param ModelAnalyzerInterface $analyzer
     * @return $this
     */
    public function setAnalyzer(ModelAnalyzerInterface $analyzer)
    {
        $this->analyzer = $analyzer;

        return $this;
    }

    /**
     * Performs analysis.
     *
     * @param ModelInformationInterface $information
     * @return ModelInformationInterface
     */
    public function analyze(ModelInformationInterface $information)
    {
        $this->info = $information;

        $this->performStep();

        return $this->info;
    }

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    abstract protected function performStep();

    /**
     * Returns instance of the model being analyzed.
     *
     * @return Model
     */
    public function model()
    {
        return $this->analyzer->model();
    }

    /**
     * Returns reflection of the class being analyzed.
     *
     * @return ReflectionClass
     */
    public function reflection()
    {
        return $this->analyzer->reflection();
    }

    /**
     * Returns configured or bound database analyzer.
     *
     * @return DatabaseAnalyzerInterface
     */
    protected function databaseAnalyzer()
    {
        return app(
            config('cms-models.analyzer.database.class') ?: DatabaseAnalyzerInterface::class
        );
    }


    /**
     * Returns whether an attribute should be taken for a boolean.
     *
     * @param ModelAttributeData $attribute
     * @return bool
     */
    protected function isAttributeBoolean(ModelAttributeData $attribute)
    {
        if ($attribute->cast == AttributeCast::BOOLEAN) {
            return true;
        }

        return $attribute->type === 'tinyint' && $attribute->length === 1;
    }

    /**
     * Returns associative array representing the CMS docblock tag content.
     *
     * @param ReflectionMethod $method
     * @return array
     */
    protected function getCmsDocBlockTags(ReflectionMethod $method)
    {
        if ( ! ($docBlock = $method->getDocComment())) {
            return [];
        }

        $factory = DocBlockFactory::createInstance();
        $doc     = $factory->create( $docBlock );

        $tags = $doc->getTagsByName('cms');

        if ( ! $tags || ! count($tags)) {
            return [];
        }

        $cmsTags = [];

        foreach ($tags as $tag) {

            if ( ! method_exists($tag, 'getDescription')) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $description = trim($tag->getDescription());

            if (preg_match('#\s+#', $description)) {
                list($firstWord, $parameters) = preg_split('#\s+#', $description, 2);
            } else {
                $firstWord  = $description;
                $parameters = '';
            }

            $firstWord = strtolower($firstWord);

            if ($firstWord == 'relation') {
                $cmsTags['relation'] = true;
                continue;
            }

            if ($firstWord == 'ignore') {
                $cmsTags['ignore'] = true;
                continue;
            }

            if ($firstWord == 'morph') {
                if ( ! $parameters) continue;

                $models = array_map('trim', explode(',', $parameters));

                if ( ! array_key_exists('morph', $cmsTags)) {
                    $cmsTags['morph'] = [];
                }

                $cmsTags['morph'] = array_merge($cmsTags['morph'], $models);
                $cmsTags['morph'] = array_unique($cmsTags['morph']);
                continue;
            }
        }

        return $cmsTags;
    }

    /**
     * Returns the PHP code for a ReflectionMethod.
     *
     * @param ReflectionMethod $method
     * @return string
     */
    protected function getMethodBody(ReflectionMethod $method)
    {
        // Get file content for the method

        $file = new SplFileObject($method->getFileName());

        $methodBodyLines = iterator_to_array(
            new LimitIterator(
                $file,
                $method->getStartLine(),
                $method->getEndLine() - $method->getStartLine()
            )
        );

        return implode("\n", $methodBodyLines);
    }

    /**
     * Returns relation method name related to a relation instance.
     *
     * @param Relation $relation
     * @return null|string
     */
    protected function getRelationNameFromRelationInstance(Relation $relation)
    {
        // Get all relations, load instances and compare them loosely
        foreach ($this->info->relations as $relationData) {

            if ($relation == $this->model()->{$relationData->method}()) {
                return $relationData->method;
            }
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Adds an entry to the default includes.
     *
     * @param string     $relation
     * @param null|mixed $value
     * @return $this
     */
    protected function addIncludesDefault($relation, $value = null)
    {
        $includes = array_get($this->info->includes, 'default', []);

        if (null !== $value) {
            $includes[ $relation ] = $value;
        } elseif ( ! array_key_exists($relation, $includes) && ! in_array($relation, $includes)) {
            $includes[] = $relation;
        }

        $this->info->includes['default'] = $includes;

        return $this;
    }

}
