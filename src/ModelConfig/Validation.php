<?php

namespace Czim\CmsModels\ModelConfig;

class Validation
{

    /**
     * @var string
     */
    protected $key;


    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public static function make(string $key): Validation
    {
        return new static($key);
    }



}
