@extends('cms::layout.error')

@section('content')

    <header>
        <h1 class="page-header text-danger">
            {{ cms_trans('common.errors.title.' . (isset($statusCode) ? (string) $statusCode : '500')) }}
        </h1>

        <div class="text-danger">
            <b>
                {!! nl2br(e($exception->getMessage())) !!}
            </b>
        </div>
    </header>

@endsection
