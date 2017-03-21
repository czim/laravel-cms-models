<?php
namespace Czim\CmsModels\ModelInformation\Analyzer\Processor\Steps;

use Czim\CmsModels\ModelInformation\Data\ModelRelationData;
use Czim\CmsModels\Support\Enums\RelationType;
use Exception;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;
use RuntimeException;

class AnalyzeRelations extends AbstractAnalyzerStep
{

    /**
     * Regular expressions that must not match a class FQN.
     *
     * If the method is in a matching class, it will not be parsed as a relation.
     *
     * @var string[]
     */
    protected $mustNotMatchClass = [
        '#^Illuminate\\\\#',
    ];

    /**
     * Regular expressions that must not match a file path.
     *
     * If the method is in a matching file, it will not be parsed as a relation.
     *
     * @var string[]
     */
    protected $mustNotMatchFile = [
        '#vendor/dimsav/laravel-translatable#',
    ];


    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        $relations = [];

        foreach ($this->reflection()->getMethods() as $method) {

            if (    ! $this->isPotentialRelationMethod($method)
                ||  ! $this->isReflectionMethodEloquentRelation($method)
            ) {
                continue;
            }

            // It's a relationship method; get information from method
            try {
                $relation = $this->model()->{$method->name}();

            } catch (Exception $e) {
                // If an exception occurs, ignore it and ignore the method
                continue;
            }

            if ( ! ($relation instanceof Relation)) {
                continue;
            }

            $type        = camel_case(class_basename($relation));
            $morphModels = [];

            // Determine if the foreign key for this relation is nullable.
            // For now, this only concerns belongsTo relations.
            $nullableKey = false;
            $foreignKeys = [];

            if ($type == RelationType::BELONGS_TO || $type == RelationType::BELONGS_TO_THROUGH) {
                /** @var $relation Relations\BelongsTo */
                $foreignKeys = [ $relation->getForeignKey() ];
                $nullableKey = $this->isNullableKey($relation->getForeignKey());

            } elseif ($type == RelationType::MORPH_TO) {
                /** @var Relations\MorphTo $relation */
                $foreignKeys = [$relation->getForeignKey(), $relation->getMorphType()];
                $nullableKey = $this->isNullableKey($relation->getForeignKey());

                $morphModels = $this->getMorphedModelsFromMorphToReflectionMethod($method);

            } elseif ($type == RelationType::HAS_ONE || $type == RelationType::HAS_MANY) {
                /** @var $relation Relations\HasMany */
                $foreignKeys = [ $relation->getQualifiedForeignKeyName() ];

            } elseif ($type == RelationType::BELONGS_TO_MANY) {
                /** @var Relations\BelongsToMany $relation */
                $foreignKeys = [
                    $relation->getQualifiedForeignKeyName(),
                    $relation->getQualifiedRelatedKeyName(),
                ];

            } elseif ($type == RelationType::MORPH_TO_MANY) {
                /** @var Relations\MorphToMany $relation */
                $foreignKeys = [
                    $relation->getQualifiedForeignKeyName(),
                    $relation->getMorphType(),
                    $relation->getQualifiedRelatedKeyName(),
                ];

                // The relation is inverse if the MorphClass is not this model
                if (get_class($this->model()) !== $relation->getMorphClass()) {
                    $type = RelationType::MORPHED_BY_MANY;
                }
            }

            $relations[ $method->name ] = new ModelRelationData([
                'name'          => $method->name,
                'method'        => $method->name,
                'type'          => $type,
                'relationClass' => get_class($relation),
                'relatedModel'  => get_class($relation->getRelated()),
                'foreign_keys'  => $foreignKeys,
                'nullable_key'  => $nullableKey,
                'morphModels'   => $morphModels,
            ]);
        }

        $this->info['relations'] = $relations;
    }

    /**
     * Returns whether a reflected method may be an Eloquent relation.
     *
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function isPotentialRelationMethod(ReflectionMethod $method)
    {
        if (
                ! $method->isPublic()
            // Check if we should ignore the method always
            ||  in_array($method->name, $this->getIgnoredRelationNames())
            // If the method has required parameters, we cannot call or use it
            ||  $method->getNumberOfRequiredParameters()
        ) {
            return false;
        }

        // Don't examine methods defined in certain namespaces
        foreach ($this->mustNotMatchClass as $regEx) {
            if (preg_match($regEx, $method->class)) {
                return false;
            }
        }

        // Don't examine methods defined in certain files
        $file = $method->getFileName();
        foreach ($this->mustNotMatchFile as $regEx) {
            if (preg_match($regEx, $file)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns list of relation names to ignore.
     *
     * @return string[]
     */
    protected function getIgnoredRelationNames()
    {
        return config('cms-models.analyzer.relations.ignore', []);
    }

    /**
     * Returns whether attribute with key is known to be nullable.
     *
     * @param string $key
     * @return bool
     */
    protected function isNullableKey($key)
    {
        if ( ! array_key_exists($key, $this->info->attributes)) {
            throw new RuntimeException(
                "Foreign key '{$key}' defined for relation does not exist on model " . get_class($this->model())
            );
        }

        return (bool) $this->info->attributes[ $key ]->nullable;
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
            "\$this->morphToMany(",
            "\$this->morphedByMany(",

            "\$this->belongsToThrough(",
        ];
    }

    /**
     * Returns list of morph related models for a MorphTo (reflected) relation.
     *
     * This only attempts to retrieve a list from docblock tags.
     * Relations themselves are only analyzed during enrichment, when all models are analyzed.
     *
     * @param ReflectionMethod $method
     * @return string[]
     */
    protected function getMorphedModelsFromMorphToReflectionMethod(ReflectionMethod $method)
    {
        $cmsTags = $this->getCmsDocBlockTags($method);

        return array_get($cmsTags, 'morph', []);
    }

}
