<?php
namespace Czim\CmsModels\View\FilterStrategies;

/**
 * Class BasicSplitString
 *
 * Basic string searching, but splits the search string into separate terms.
 */
class BasicSplitString extends BasicString
{

    /**
     * Whether to split the terms and search for them separately
     *
     * @var bool
     */
    protected $splitTerms = true;

}
