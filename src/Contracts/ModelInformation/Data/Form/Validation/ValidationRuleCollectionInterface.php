<?php
namespace Czim\CmsModels\Contracts\ModelInformation\Data\Form\Validation;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

interface ValidationRuleCollectionInterface extends ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{

}
