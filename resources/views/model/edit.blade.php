@extends(cms_config('views.layout'))

<?php
    if ($creating) {
        $title = $model->verbose_name . ' - ' . cms_trans('common.action.create');
    } else {
        $title = $model->verbose_name . ' (' . $record->getKey() .  ') - ' . cms_trans('common.action.edit');
    }
?>

@section('title', $title)

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li>
            <a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
                {{ cms_trans('common.home') }}
            </a>
        </li>
        <li>
            <a href="{{ cms_route("{$routePrefix}.index") }}">
                {{ $model->verbose_name_plural }}
            </a>
        </li>
        <li class="active">
            {{ $title }}
        </li>
    </ol>
@endsection


{{-- Only show general errors at form top; the field errors are displayed in the field partial itself --}}
@section('errors')

    @if (isset($errors) && $errors->has('__general__'))

        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

            @foreach ($errors->get('__general__') as $err)
                <p>{{ $err }}</p>
            @endforeach

        </div>
    @endif

@stop


@section('content')

    <div class="page-header">
        <h1>{{ $title }}</h1>
    </div>

    @if ($creating)
        <form class="model-form" method="post" action="{{ cms_route("{$routePrefix}.store") }}">
    @else
        <form class="model-form" method="post" action="{{ cms_route("{$routePrefix}.update", [ $record->getKey() ]) }}">
            {{ method_field('put') }}
    @endif
            {{ csrf_field() }}

            <input id="edit-form-save-and-close-input" type="hidden" name="__save_and_close__" value="0">


        @if ($model->form->hasTabs())

            <?php
                $tabs = $model->form->tabs();
            ?>

            <div class="tab-container">

                @include('cms-models::model.partials.form.tab_lips', compact(
                    'record',
                    'model',
                    'tabs',
                    'routePrefix',
                    'permissionPrefix',
                    'errorsPerTab'
                ))

                @include('cms-models::model.partials.form.tab_panes', array_merge(
                    compact(
                        'record',
                        'model',
                        'values',
                        'fieldErrors',
                        'tabs',
                        'routePrefix',
                        'permissionPrefix',
                        'errorsPerTab'
                    ),
                    [ 'errors' => $fieldErrors ]
                ))

            </div>

        @else

            @foreach ($model->form->layout() as $nodeKey => $node)

                    @include('cms-models::model.partials.form.layout_node', array_merge(
                        compact(
                            'node',
                            'nodeKey',
                            'record',
                            'model',
                            'values'
                        ),
                        [
                            'errors' => $fieldErrors,
                            'parent' => null,
                        ]
                    ))

            @endforeach
        @endif

        @include('cms-models::model.partials.form.buttons', compact('record', 'model'))

    </form>

@endsection

@push('javascript-end')

    <script>
        $(function () {
            $('.translated-form-field-locale-option > a').click(function () {

                var locale = $(this).attr('data-locale');

                var activeButtons = $('.translated-form-field-locale-select > button');
                activeButtons.find('img').attr('src', $(this).attr('data-asset'));

                $('.translated-form-field-locale-option').show();
                $('.translated-form-field-locale-option[data-locale=' + locale + ']').hide();

                $('.translated-form-field-wrapper').hide();
                $('.translated-form-field-wrapper[data-locale=' + locale + ']').show();
            });
        });
    </script>
@endpush
