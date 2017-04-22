<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>CMS - @yield('title', 'Dashboard')</title>

    @stack('javascript-head')

</head>
<body id="app-layout">

<div id="wrapper">

    <div id="page-wrapper">
        <div class="container-fluid">

            @yield('breadcrumbs')

            @include('cms::layout.partials.flash-messages')

            @section('errors')
                @include('cms::layout.errors')
            @show

            @yield('content')

        </div>
    </div>

</div>

@stack('javascript-end')

</body>
</html>
