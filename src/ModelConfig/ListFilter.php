<?php
namespace Czim\CmsModels\ModelConfig;

class ListFilter
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @var array
     */
    protected $main = [];


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


    /**
     * Display label for the filter (or translation key)
     *
     * @param string $label literal label
     * @return ListFilter|$this
     */
    public function literalLabel(string $label): ListFilter
    {
        $this->main['label'] = $label;

        return $this;
    }

    /**
     * Display label for the filter (or translation key)
     *
     * @param string $label translation key
     * @return ListFilter|$this
     */
    public function translatedName(string $label): ListFilter
    {
        $this->main['translated_label'] = $label;

        return $this;
    }

    /**
     * Set the filter target
     *
     * @param string $target attribute, column, relation or other strategy to filter against
     * @return ListFilter|$this
     */
    public function target(string $target): ListFilter
    {
        $this->main['target'] = $target;

        return $this;
    }

    /**
     * If any known, the source that the filter was created for.
     *
     * Should not be confused with the filter target (by which filtering actually occurs!)
     *
     * This need not be set, unless automatically by model analysis.
     *
     * @param string $source attribute or relationship name
     * @return ListFilter|$this
     */
    public function source(string $source): ListFilter
    {
        $this->main['source'] = $source;

        return $this;
    }


    /**
     * Set the filter strategy
     *
     * @param string $strategy  alias or FQN
     * @param array  $options   options specific for the strategy
     * @return ListFilter|$this
     */
    public function strategy(string $strategy, array $options = []): ListFilter
    {
        $this->main['strategy'] = $strategy;

        if (count($options)) {
            $this->main['options'] = $options;
        }

        return $this;
    }

}
