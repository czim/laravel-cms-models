<?php
namespace Czim\CmsModels\ModelConfig;

class ListFilter
{

    /**
     * @var string
     */
    protected $key;


    public function __construct(string $key)
    {
        $this->key = $key;
    }


    public static function make(string $key): ListFilter
    {
        return new static($key);
    }


    public function getKey(): string
    {
        return $this->key;
    }

    public function toArray(): array
    {
        // todo
        return [];
    }

}
