@extends(cms_config('views.layout'))

@php
    if ($creating) {
        $title = ucfirst(cms_trans('common.action.create')) . ' ' . $model->label();
    } else {
        $title = ucfirst(cms_trans('common.action.edit'))
               . ' ' . $model->label()
               . ' ' . (trim($recordReference) ?: ($record->incrementing ? '#' . $record->getKey() : "'" . $record->getKey() .  "'"));
    }
@endphp

@section('title', $title)

@section('breadcrumbs')
    @include('cms-models::model.partials.detail_breadcrumbs', compact(
        'model',
        'routePrefix',
        'title',
        'hasActiveListParent',
        'listParents'
    ))
@endsection


{{-- Only show general errors at form top; the field errors are displayed in the field partial itself --}}
@section('errors')

    @if (isset($errors) && $errors->has('__general__'))

        <div class="alert alert-danger">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>

            @foreach ($errors->get('__general__') as $err)
                <p>{{ $err }}</p>
            @endforeach

            @if (cms_auth()->admin())
                <div id="form-field-general-errors-full-toggle" class="show text-small">
                    <small>
                        <a href="#" class="text-danger">
                            {{ cms_trans('common.errors.form.show-all-errors') }}
                            <span class="full-toggle-closed caret"></span>
                        </a>
                    </small>
                </div>
                <div id="form-field-general-errors-full" style="display: none">
                    @include('cms-models::model.partials.form.general_errors', compact('errors'))
                </div>
            @endif
        </div>
    @endif

@stop


@section('content')

    <div class="page-header">
        <h1>{{ $title }}</h1>
    </div>

    {{-- Before view --}}
    @if ($model->form->before && $model->form->before->view)
        @include($model->form->before->view, $model->form->before->variables())
    @endif


    @if ($creating)
        <form class="model-form"
              method="post"
              action="{{ cms_route("{$routePrefix}.store") }}"
              enctype="multipart/form-data"
              novalidate
              data-id=""
              data-class="{{ $model->modelClass() }}"
        >
    @else
        <form class="model-form"
              method="post"
              action="{{ cms_route("{$routePrefix}.update", [ $record->getKey() ]) }}"
              enctype="multipart/form-data"
              novalidate
              data-id="{{ $record->getKey() }}"
              data-class="{{ $model->modelClass() }}"
        >
            {{ method_field('put') }}
    @endif
            {{ csrf_field() }}

            <input id="edit-form-save-and-close-input" type="hidden" name="__save_and_close__" value="0">
            <input id="edit-form-active-tab-input" type="hidden" name="__active_tab__"
                   value="{{ old(\Czim\CmsModels\Http\Controllers\DefaultModelController::ACTIVE_TAB_PANE_KEY) }}">
            <input id="edit-form-active-translation-locale-input" type="hidden" name="__active_translation_locale__" value="">

        {{-- Before view in form --}}
        @if ($model->form->before_form && $model->form->before_form->view)
            @include($model->form->before_form->view, $model->form->before_form->variables())
        @endif


        @if ($model->form->hasTabs())

            @php
                $tabs = $model->form->tabs();
            @endphp

            <div class="tab-container">

                @include('cms-models::model.partials.form.tab_lips', compact(
                    'record',
                    'model',
                    'tabs',
                    'routePrefix',
                    'permissionPrefix',
                    'activeTab',
                    'errorsPerTab'
                ))

                @include('cms-models::model.partials.form.tab_panes', array_merge(
                    compact(
                        'record',
                        'model',
                        'fields',
                        'fieldStrategies',
                        'values',
                        'fieldErrors',
                        'tabs',
                        'routePrefix',
                        'permissionPrefix',
                        'activeTab',
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
                            'fields',
                            'fieldStrategies',
                            'values'
                        ),
                        [
                            'errors' => $fieldErrors,
                            'parent' => null,
                        ]
                    ))

            @endforeach
        @endif


        {{-- After view in form --}}
        @if ($model->form->after_form && $model->form->after_form->view)
            @include($model->form->after_form->view, $model->form->after_form->variables())
        @endif


        @include('cms-models::model.partials.form.buttons', compact('record', 'model'))

    </form>

    {{-- After view --}}
    @if ($model->form->after && $model->form->after->view)
        @include($model->form->after->view, $model->form->after->variables())
    @endif

@endsection

@cms_script
    <script>
        $(function () {

            // Toggle showing full errors in the error alert
            $('#form-field-general-errors-full-toggle').click(function () {
                $('#form-field-general-errors-full').toggle();
                $(this).find('.full-toggle-closed').toggle();
            });

            // Switch locales for translations
            $(document).on('click', '.translated-form-field-locale-option > a', function () {

                var locale = $(this).attr('data-locale');

                var activeButtons = $('.translated-form-field-locale-select > button');
                activeButtons.find('img').attr('src', $(this).attr('data-asset'));

                $('.translated-form-field-locale-option').show();
                $('.translated-form-field-locale-option[data-locale=' + locale + ']').hide();

                $('.translated-form-field-wrapper').hide();
                $('.translated-form-field-wrapper[data-locale=' + locale + ']').show();

                // Update field to submit active locale
                $('#edit-form-active-translation-locale-input').val(locale);
            });

            $('.edit-form-tab-lip').click(function () {
                var input = $('#edit-form-active-tab-input');
                if (input.length) {
                    input.val( $(this).attr('data-key') );
                }
            });
        });
    </script>
@cms_endscript
