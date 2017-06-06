@php
    /** @var \Czim\CmsModels\Contracts\Strategies\ListDisplayInterface $strategy */
    /** @var \Czim\CmsModels\ModelInformation\Data\Listing\ModelListColumnData $column */
    /** @var \Illuminate\Database\Eloquent\Model $record */
    /** @var bool $hasDefaultAction */

    $attributes = [
        'class' => trim('column ' . $column->style . ' ' . $strategy->style($record, $column->source))
                 . ($hasDefaultAction && ! $column->disable_default_action ? ' default-action' : null),
    ];

    $attributes = array_merge($attributes, $strategy->attributes($record, $column->source));
    $attributes = implode(
        ' ',
        array_map(
            function ($key, $value) { return e($key) . '="' . e($value) . '"'; },
            array_keys($attributes),
            array_values($attributes)
        )
    );
@endphp

<td {!! $attributes !!}>
    @php
        try {
            echo $strategy->render($record, $column->source);

        } catch (\Exception $e) {

            throw new \Czim\CmsModels\Exceptions\StrategyRenderException(
                "Issue rendering list column '{$key}' (list.columns.{$key}, using "
                . get_class($strategy) . "): \n{$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    @endphp
</td>
