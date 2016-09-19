<?php
namespace Czim\CmsModels\Analyzer;

use Codesleeve\Stapler\Interfaces\Attachment;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Czim\CmsModels\Contracts\Analyzer\DatabaseAnalyzerInterface;
use Czim\CmsModels\Contracts\Data\ModelInformationInterface;
use Czim\CmsModels\Support\Data\Analysis\StaplerAttachment;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelInformation;
use Czim\CmsModels\Support\Data\ModelRelationData;
use Czim\CmsModels\Support\Data\ModelScopeData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Czim\CmsModels\Support\Enums\AttributeFormStrategy;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use LimitIterator;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use SplFileObject;
use UnexpectedValueException;

class ModelAnalyzer
{

    /**
     * @var DatabaseAnalyzerInterface
     */
    protected $databaseAnalyzer;

    /**
     * @var TranslationAnalyzer
     */
    protected $translationAnalyzer;

    /**
     * @var AttributeStrategyResolver
     */
    protected $attributeStrategyResolver;

    /**
     * @var RelationStrategyResolver
     */
    protected $relationStrategyResolver;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var ReflectionClass
     */
    protected $reflection;

    /**
     * @var ModelInformation
     */
    protected $info;


    /**
     * @param DatabaseAnalyzerInterface $databaseAnalyzer
     * @param TranslationAnalyzer       $translationAnalyzer
     * @param AttributeStrategyResolver $attributeStrategyResolver
     * @param RelationStrategyResolver  $relationStrategyResolver
     */
    public function __construct(
        DatabaseAnalyzerInterface $databaseAnalyzer,
        TranslationAnalyzer $translationAnalyzer,
        AttributeStrategyResolver $attributeStrategyResolver,
        RelationStrategyResolver $relationStrategyResolver
    ) {
        $this->databaseAnalyzer          = $databaseAnalyzer;
        $this->translationAnalyzer       = $translationAnalyzer;
        $this->attributeStrategyResolver = $attributeStrategyResolver;
        $this->relationStrategyResolver  = $relationStrategyResolver;

        $this->translationAnalyzer->setModelAnalyzer(clone $this);
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
             ->analyzeGlobalScopes()
             ->analyzeAttributes()
             ->analyzeTraits()
             ->analyzeScopes()
             ->analyzeRelationships()
             ->analyzeTranslation();

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

        $this->info['verbose_name']        = strtolower(snake_case(class_basename($this->model), ' '));
        $this->info['verbose_name_plural'] = str_plural($this->info['verbose_name']);

        $this->info['incrementing']      = $this->model->getIncrementing();
        $this->info['timestamps']        = $this->model->usesTimestamps();
        $this->info['timestamp_created'] = $this->model->getCreatedAtColumn();
        $this->info['timestamp_updated'] = $this->model->getUpdatedAtColumn();

        return $this;
    }

    /**
     * Analyzes any global scopes set on the model.
     *
     * @return $this
     */
    protected function analyzeGlobalScopes()
    {
        // If the model has global scopes, the default CMS settings is to disable all of them.

        if (count($this->model->getGlobalScopes())) {
            $this->info->meta->disable_global_scopes = true;
        }

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

            $length = $field['length'];

            $cast = $this->getAttributeCastForColumnType($field['type'], $length);

            $attributes[ $field['name'] ] = new ModelAttributeData([
                'name'     => $field['name'],
                'cast'     => $cast,
                'type'     => $field['type'],
                'nullable' => $field['nullable'],
                'unsigned' => $field['unsigned'],
                'length'   => $length,
                'values'   => $field['values'],
            ]);
        }

        foreach ($attributes as $attribute) {
            $attribute['strategy'] = $this->attributeStrategyResolver->determineStrategy($attribute);
        }

        // Activatable column detection
        $activeColumn = $this->getActivateColumnName();
        foreach ($attributes as $name => $attribute) {
            if ($name == $activeColumn && $this->isAttributeBoolean($attribute)) {
                $this->info->list->activatable   = true;
                $this->info->list->active_column = $name;
                break;
            }
        }

        // Stapler / attachment attributes
        $attachments = $this->detectStaplerAttachments();

        foreach ($attachments as $key => $attachment) {

            $strategy = $attachment->image
                ?   AttributeFormStrategy::ATTACHMENT_STAPLER_IMAGE
                :   AttributeFormStrategy::ATTACHMENT_STAPLER_FILE;

            $attribute = new ModelAttributeData([
                'name'     => $key,
                'cast'     => null,
                'type'     => null,
                'strategy' => $strategy,
            ]);

            $attributes = $this->insertInArray($attributes, $key, $attribute, $key . '_file_name');
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
     * @return string
     */
    protected function getActivateColumnName()
    {
        return 'active';
    }

    /**
     * Returns list of stapler attachments, if the model has any.
     *
     * @return StaplerAttachment[]  assoc, keyed by attribute name
     */
    protected function detectStaplerAttachments()
    {
        if ( ! ($this->model instanceof StaplerableInterface)) {
            return [];
        }

        $files = $this->model->getAttachedFiles();

        $attachments = [];

        foreach ($files as $attribute => $file) {
            /** @var Attachment $file */
            $styles = $file->getConfig()->styles;

            $normalizedStyles = [];
            foreach ($styles as $style) {
                if ($style->name === 'original') continue;
                $normalizedStyles[ $style->name ] = $style->dimensions;
            }

            $attachments[ $attribute ] = new StaplerAttachment([
                'image'   => (is_array($styles) && count($styles) > 1),
                'resizes' => $normalizedStyles,
            ]);
        }

        return $attachments;
    }

    /**
     * @param string $type
     * @param null   $length
     * @return string
     */
    protected function getAttributeCastForColumnType($type, $length = null)
    {
        switch ($type) {

            case 'bool':
                return AttributeCast::BOOLEAN;

            case 'tinyint':
                if ($length === 1) {
                    return AttributeCast::BOOLEAN;
                }
                return AttributeCast::INTEGER;

            case 'int':
            case 'integer':
            case 'mediumint':
            case 'smallint':
            case 'bigint':
                return AttributeCast::INTEGER;

            case 'dec':
            case 'decimal':
            case 'double':
            case 'float':
                return AttributeCast::FLOAT;

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
                return AttributeCast::STRING;

            case 'date':
            case 'datetime':
            case 'time':
            case 'timestamp':
                return AttributeCast::DATE;

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

        if (in_array('Dimsav\\Translatable\\Translatable', $traitNames)) {
            $this->info->translated           = true;
            $this->info->translation_strategy = 'translatable';
        }

        if (    in_array('Czim\\Listify\\Listify', $traitNames)
            ||  in_array('Lookitsatravis\\Listify\\Listify', $traitNames)
        ) {
            $this->info->list->orderable      = true;
            $this->info->list->order_strategy = 'listify';
            $this->info->list->order_column   = $this->model->positionColumn();
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
            $scopes[ $scopeName ] = new ModelScopeData([
                'method'   => $scopeName,
                'label'    => null,
                'strategy' => null,
            ]);
        }

        $this->info->list->scopes = $scopes;

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

            $relations[ $method->name ] = new ModelRelationData([
                'name'          => snake_case($method->name),
                'method'        => $method->name,
                'type'          => camel_case(class_basename($relation)),
                'relationClass' => get_class($relation),
                'relatedModel'  => get_class($relation->getRelated()),
            ]);
        }

        foreach ($relations as $relation) {
            $relation['strategy_list'] = $this->relationStrategyResolver->determineListStrategy($relation);
            $relation['strategy_form'] = $this->relationStrategyResolver->determineFormStrategy($relation);
        }

        $this->info['relations'] = $relations;
        
        return $this;
    }

    /**
     * Analyzes the related translation, if this model is translated.
     *
     * @return $this
     */
    protected function analyzeTranslation()
    {
        if ($this->info->translated) {

            $translationInfo = $this->translationAnalyzer->analyze($this->model);

            $attributes = $this->info['attributes'];

            // Mark the fillable fields on the translation model
            foreach ($translationInfo['attributes'] as $key => $attribute) {
                /** @var ModelAttributeData $attribute */
                if ( ! $attribute->translated) continue;

                if ( ! isset($attributes[$key])) {
                    $attributes[$key] = $attribute;
                }

                /** @var ModelAttributeData $attributeData */
                $attributeData = $attributes[$key];
                $attributeData->merge($attribute);
                $attributes[$key] = $attributeData;
            }

            $this->info['attributes'] = $attributes;
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
            throw new UnexpectedValueException("Class '{$this->class}' does not exist");
        }

        $instance = new $class;

        if ( ! $instance instanceof Model) {
            throw new UnexpectedValueException("Instance of '{$this->class}' is not a model");
        }

        $this->model = $instance;

        $this->reflection = new ReflectionClass($this->class);

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


    /**
     * Insert an item into an associative array at the position before a given key.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     * @param string $beforeKey
     * @return array
     */
    protected function insertInArray($array, $key, $value, $beforeKey)
    {
        // Find the position of the array
        $position = array_search($beforeKey, array_keys($array));

        if (false === $position) {
            $array[ $key ] = $value;
            return $array;
        }

        if (0 === $position) {
            return [ $key => $value ] + $array;
        }

        // Slice the array up with the new entry in between
        return array_slice($array, 0, $position, true)
             + [ $key => $value ]
             + array_slice($array, $position, count($array) - $position, true);
    }
}
