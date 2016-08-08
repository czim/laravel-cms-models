<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Illuminate\Database\Eloquent\Model;
use LimitIterator;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionMethod;
use SplFileObject;

class ModelAnalyzer
{

    /**
     * @var DatabaseAnalyzerInterface
     */
    protected $databaseAnalyzer;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var ModelInformation
     */
    protected $info;


    /**
     * @param DatabaseAnalyzerInterface $databaseAnalyzer
     */
    public function __construct(DatabaseAnalyzerInterface $databaseAnalyzer)
    {
        $this->databaseAnalyzer = $databaseAnalyzer;
    }

    /**
     * Analyzes a model and returns normalized information about it.
     *
     * @param string $modelClass    FQN of model to analyze
     * @return ModelInformationInterface
     */
    public function analyze($modelClass)
    {
        $this->class = $modelClass;

        $this->info = new ModelInformation([]);

        $this->makeModelInstance()
             ->fillBasicInformation()
             ->analyzeAttributes()
             ->analyzeTraits()
             ->analyzeRelationships();

        return $this->info;
    }


    /**
     * Analyzes and fills basic information about the model.
     *
     * @return $this
     */
    protected function fillBasicInformation()
    {
        $this->info['model']          = $this->class;
        $this->info['original_model'] = $this->class;

        $this->info['verbose_name']        = strtolower(class_basename($this->model));
        $this->info['verbose_name_plural'] = str_plural($this->info['verbose_name']);

        $this->info['incrementing']      = $this->model->getIncrementing();
        $this->info['timestamps']        = $this->model->usesTimestamps();
        $this->info['timestamp_created'] = $this->model->getCreatedAtColumn();
        $this->info['timestamp_updated'] = $this->model->getUpdatedAtColumn();

        return $this;
    }

    /**
     * Analyzes the model's attributes.
     *
     * @return $this
     */
    protected function analyzeAttributes()
    {
        $attributes = [];

        // Get the columns from the model's table
        $tableFields = $this->databaseAnalyzer->getColumns($this->model->getTable());

        foreach ($tableFields as $field) {

            $cast = $this->getAttributeTypeForColumnType($field['type']);

            $attributes[ $field['name'] ] = new ModelAttributeData([
                'name'     => $field['name'],
                'cast'     => $cast,
                'type'     => $field['type'],
                'nullable' => $field['nullable'],
                'unsigned' => $field['unsigned'],
                'length'   => $field['length'],
                'values'   => $field['values'],
            ]);
        }

        foreach ($this->model->getFillable() as $attribute) {
            if ( ! isset($attributes[ $attribute ])) continue;
            $attributes[ $attribute ]['fillable'] = true;
        }

        foreach ($this->model->getCasts() as $attribute => $cast) {
            if ( ! isset($attributes[ $attribute ])) continue;
            $attributes[ $attribute ]['cast'] = $cast;
        }

        $this->info['attributes'] = $attributes;

        return $this;
    }

    /**
     * @param string $type
     * @param null   $length
     * @return string
     */
    protected function getAttributeTypeForColumnType($type, $length = null)
    {
        switch ($type) {

            case 'bool':
            case 'tinyint':
                if ($length === 1) {
                    return 'boolean';
                }
                return 'int';

            case 'int':
            case 'integer':
            case 'mediumint':
            case 'smallint':
            case 'bigint':
                return 'int';

            case 'dec':
            case 'decimal':
            case 'double':
            case 'float':
                return 'float';

            case 'varchar':
            case 'char':
            case 'enum':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'tinytext':
            case 'year':
            case 'blob';
            case 'mediumblob';
            case 'longblob';
            case 'binary';
            case 'varbinary';
                return 'string';

            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
                return 'date';

            default:
                return $type;
        }
    }

    /**
     * Analyzes the model's traits.
     *
     * @return $this
     */
    protected function analyzeTraits()
    {
        $traitNames = $this->reflection->getTraitNames();

        if (in_array("Dimsav\\Translatable\\Translatable", $traitNames)) {
            $this->info['translated']           = true;
            $this->info['translation_strategy'] = 'translatable';
        }

        return $this;
    }

    /**
     * Analyzes the model's relations.
     *
     * @return $this
     */
    protected function analyzeRelationships()
    {
        foreach ($this->reflection->getMethods() as $method) {

            // Relations should only be expected on the model itself
            if ($method->getDeclaringClass()->name !== $this->class) {
                continue;
            }

            if ( ! $this->isReflectionMethodEloquentRelation($method)) {
                continue;
            }

            // todo
            // it's a relationship method; get information from method
        }

        return $this;
    }

    /**
     * Determines whether a given method is a typical relation method
     *
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function isReflectionMethodEloquentRelation(ReflectionMethod $method)
    {
        // Check if there is a docblock cms tag
        // this may either 'ignore' the method, or confirm it as a 'relation'

        if ($docBlock = $method->getDocComment()) {
            $factory = DocBlockFactory::createInstance();
            $doc     = $factory->create( $docBlock );

            $tags = $doc->getTagsByName('cms');

            if ($tags && count($tags)) {
                foreach ($tags as $tag) {

                    $description = strtolower(trim($tag->getDescription()));

                    if ($description == 'relation') {
                        return true;
                    } elseif ($description == 'ignore') {
                        return false;
                    }
                }
            }
        }

        // analyze the method to see whether it is a relation
        $body = $this->getMethodBody($method);

        var_dump($method->name);
        dd($body);

        return false;
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
     * Makes instance of the model class and stores it.
     *
     * @return $this
     */
    protected function makeModelInstance()
    {
        $class = $this->class;

        if ( ! class_exists($class)) {
            throw new \UnexpectedValueException("Class '{$this->class}' does not exist");
        }

        $instance = new $class;

        if ( ! $instance instanceof Model) {
            throw new \UnexpectedValueException("Instance of '{$this->class}' is not a model");
        }

        $this->model = $instance;

        $this->reflection = new \ReflectionClass($this->class);

        return $this;
    }

}
