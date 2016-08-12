<?php
namespace Czim\CmsModels\Analyzer;

use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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
             ->analyzeScopes()
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
     * Analyzes the model's scopes.
     *
     * @return $this
     */
    protected function analyzeScopes()
    {
        $scopes = [];

        foreach ($this->reflection->getMethods() as $method) {

            if ( ! starts_with($method->name, 'scope')) {
                continue;
            }

            $scopeName = camel_case(substr($method->name, 5));

            $cmsTags = $this->getCmsDocBlockTags($method);

            if (    array_get($cmsTags, 'ignore')
                ||  (   ! array_get($cmsTags, 'scope')
                    &&  in_array($scopeName, $this->getIgnoredScopeNames() )
                )
            ) {
                continue;
            }

            // store the scope name without the scope prefix
            $scopes[] = $scopeName;
        }

        $this->info['scopes'] = $scopes;

        return $this;
    }

    /**
     * Analyzes the model's relations.
     *
     * @return $this
     */
    protected function analyzeRelationships()
    {
        $relations = [];

        foreach ($this->reflection->getMethods() as $method) {

            if (
                // Relations should only be expected on the model itself
                    $method->getDeclaringClass()->name !== $this->class
                // Check if we should ignore the method always
                ||  in_array($method->name, $this->getIgnoredRelationNames())
                // If the method has required parameters, we cannot call or use it
                ||  $method->getNumberOfRequiredParameters()
                // Skip anything that is not detected as a relation method
                ||  ! $this->isReflectionMethodEloquentRelation($method)
            ) {
                continue;
            }

            // It's a relationship method; get information from method
            try {
                $relation = $this->model->{$method->name}();

            } catch (Exception $e) {
                // If an exception occurs, ignore it and ignore the method
                continue;
            }

            if ( ! ($relation instanceof Relation)) {
                continue;
            }

            $relations[ $method->name ] = [
                'name'          => snake_case($method->name),
                'method'        => $method->name,
                'type'          => camel_case(class_basename($relation)),
                'relationClass' => get_class($relation),
                'relatedModel'  => get_class($relation->getRelated()),
            ];
        }

        $this->info['relations'] = $relations;
        
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

        $cmsTags = $this->getCmsDocBlockTags($method);

        if (array_get($cmsTags, 'relation')) {
            return true;
        }

        if (array_get($cmsTags, 'ignore')) {
            return false;
        }

        // analyze the method to see whether it is a relation
        $body = $this->getMethodBody($method);

        return (bool) $this->findRelationMethodCall($body);
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
            $description = strtolower(trim($tag->getDescription()));

            if ($description == 'relation') {
                $cmsTags['relation'] = true;
                continue;
            }

            if ($description == 'scope') {
                $cmsTags['scope'] = true;
                continue;
            }

            if ($description == 'ignore') {
                $cmsTags['ignore'] = true;
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
     * Attempts to find the relation method call in a string method body.
     *
     * This looks for $this->belongsTo(...) and the like.
     *
     * @param string $methodBody
     * @return string|false
     */
    protected function findRelationMethodCall($methodBody)
    {
        $methodBody = trim(str_replace("\n", '', $methodBody));

        // find the last potential relation method opener and position
        $foundOpener  = null;
        $lastPosition = -1;

        foreach ($this->relationMethodCallOpeners() as $opener) {

            if (    false === ($pos = strrpos($methodBody, $opener))
                ||  $pos <= $lastPosition
            ) {
                continue;
            }

            $foundOpener  = $opener;
            $lastPosition = $pos;
        }


        if ( ! $foundOpener || $lastPosition < 0) {
            return false;
        }

        // todo further checks to prevent false positives
        // use https://github.com/nikic/PHP-Parser/

        return $foundOpener;
    }

    /**
     * Returns openers for relation instance Eloquent calls.
     *
     * If these are found in a method body, the method may be a relation method.
     *
     * @return string[]
     */
    protected function relationMethodCallOpeners()
    {
        return [
            "\$this->hasOne(",
            "\$this->hasMany(",
            "\$this->belongsTo(",
            "\$this->belongsToMany(",
            "\$this->morphTo(",
            "\$this->morphOne(",
            "\$this->morphMany(",

            "\$this->belongsToThrough(",
        ];
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

    /**
     * Returns list of scope names to ignore.
     *
     * @return string[]
     */
    protected function getIgnoredScopeNames()
    {
        return config('cms-models.analyzer.scopes.ignore', []);
    }

    /**
     * Returns list of relation names to ignore.
     *
     * @return mixed
     */
    protected function getIgnoredRelationNames()
    {
        return config('cms-models.analyzer.relations.ignore', []);
    }
}
