<?php
namespace Czim\CmsModels\Analyzer\Processor\Steps;

use Czim\CmsModels\Support\Data\ModelScopeData;
use ReflectionMethod;

class AnalyzeScopes extends AbstractAnalyzerStep
{

    /**
     * Performs the analyzer step on the stored model information instance.
     */
    protected function performStep()
    {
        $scopes = [];

        foreach ($this->reflection()->getMethods() as $method) {

            if ( ! starts_with($method->name, 'scope') || ! $this->isScopeMethodUsable($method)) {
                continue;
            }

            $scopeName = camel_case(substr($method->name, 5));

            // Store the scope name without the scope prefix
            $scopes[ $scopeName ] = new ModelScopeData([
                'method'   => $scopeName,
                'label'    => null,
                'strategy' => null,
            ]);
        }

        $this->info->list->scopes = $scopes;
    }

    /**
     * Returns whether a reflection method is usabled as a CMS scope.
     *
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function isScopeMethodUsable(ReflectionMethod $method)
    {
        // Scope methods with more or less required parameters than the query, should not be used.
        if ($method->getNumberOfRequiredParameters() != 1) {
            return false;
        }

        // If the required parameter is not the first, don't use it (safeguard)
        if ( ! ($firstParameter = head($method->getParameters())) || $firstParameter->isOptional()) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        $scopeName = camel_case(substr($method->name, 5));

        // Ignore scope methods with an explicity ignore cms docblock tag
        $cmsTags = $this->getCmsDocBlockTags($method);

        if (    array_get($cmsTags, 'ignore')
            ||  (   ! array_get($cmsTags, 'scope')
                &&  in_array($scopeName, $this->getIgnoredScopeNames() )
            )
        ) {
            return false;
        }

        return true;
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

}
