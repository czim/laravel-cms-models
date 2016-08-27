
<?php
    $strategy = app(\Czim\CmsModels\Contracts\View\FilterStrategyInterface::class);
?>

{!! $strategy->render(
    $filter->strategy,
    $key,
    $value,
    $filter
) !!}
