<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CMS - Error</title>

    @stack('javascript-head')
</head>
<body id="app-layout">

<?php
    // Check if the home route is even available at this point
    $homeRoute = null;

    if (app()->bound(\Czim\CmsCore\Support\Enums\Component::CORE)) {
        $routePrefix = app(\Czim\CmsCore\Support\Enums\Component::CORE)->config('route.name-prefix');

        if (app('router')->has($routePrefix . \Czim\CmsCore\Support\Enums\NamedRoute::HOME)) {
            $homeRoute = cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME);
        }
    }
?>

<div id="wrapper">

    <div id="page-wrapper">
        <div class="container-fluid">

            @yield('content')

        </div>
    </div>

</div>

@stack('javascript-end')

</body>
</html>
