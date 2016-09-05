
<?php
    $strategy = app(\Czim\CmsModels\Contracts\View\ListStrategyInterface::class);

    $attributes = [
        'class' => trim('column ' . $column->style . ' ' . $strategy->style($record, $column->strategy, $column->source)),
    ];

    $attributes = array_merge($attributes, $strategy->attributes($record, $column->strategy, $column->source));
?>

<td
        @foreach ($attributes as $attributeKey => $attributeValue)
            {{ $attributeKey }}="{{ $attributeValue }}"
        @endforeach
>
    {!! $strategy->render(
            $record,
            $column->strategy,
            $column->source
       )
    !!}
</td>
