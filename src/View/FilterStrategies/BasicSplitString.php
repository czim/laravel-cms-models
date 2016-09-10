<?php
namespace Czim\CmsModels\View\FilterStrategies;

use Czim\CmsModels\Contracts\Data\ModelFilterDataInterface;
use Czim\CmsModels\Support\Data\ModelAttributeData;
use Czim\CmsModels\Support\Data\ModelListFilterData;
use Czim\CmsModels\Support\Enums\AttributeCast;
use Illuminate\Database\Eloquent\Builder;

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
