<?php
    /** @var \Czim\CmsModels\Contracts\View\ListDisplayInterface $strategy */
    /** @var \Czim\CmsModels\Support\Data\ModelListColumnData $column */
    /** @var \Illuminate\Database\Eloquent\Model $record */
    /** @var bool $hasDefaultAction */

    $attributes = [
        'class' => trim('column ' . $column->style . ' ' . $strategy->style($record, $column->source))
                 . ($hasDefaultAction && ! $column->disable_default_action ? ' default-action' : null),
    ];

    $attributes = array_merge($attributes, $strategy->attributes($record, $column->source));
?>

<td
        @foreach ($attributes as $attributeKey => $attributeValue)
            {{ $attributeKey }}="{{ $attributeValue }}"
        @endforeach
>
    {!! $strategy->render($record, $column->source) !!}
</td>
