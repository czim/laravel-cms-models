
<div class="translated-form-field-container">

    <?php
        /** @var \Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface $helper */
        $helper = app(\Czim\CmsModels\Contracts\Support\Translation\TranslationLocaleHelperInterface::class);
        $currentLocale      = $helper->activeLocale();
        $translationLocales = $helper->availableLocales();
    ?>

    {{-- for each active locale show the pre-rendered form field strategy view --}}
    @foreach ($translationLocales as $locale)

        <div class="translated-form-field-wrapper"
             data-locale="{{ $locale }}"
             @if($currentLocale != $locale) style="display: none" @endif
        >
            {!! $localeRendered[ $locale ] !!}
        </div>

    @endforeach


    {{-- locale switcher --}}
    @if (count($translationLocales) > 1)
        <div class="dropdown translated-form-field-locale-select">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                <img src="{{ asset("_cms/img/flags/{$currentLocale}.png") }}" title="{{ $currentLocale }}">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                @foreach ($translationLocales as $locale)

                    <li role="presentation"
                        class="translated-form-field-locale-option"
                        data-locale="{{ $locale }}"
                        @if ($locale === $currentLocale) style="display: none" @endif
                    >
                        <a role="menuitem" tabindex="-1" href="#"
                           data-locale="{{ $locale }}"
                           data-asset="{{ asset("_cms/img/flags/{$locale}.png") }}"
                        >
                            <img src="{{ asset("_cms/img/flags/{$locale}.png") }}" title="{{ $locale }}">
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

{{-- If there are errors for any of the translated fields, make sure the error status is visible --}}
<div>
    @foreach ($translationLocales as $locale)
        @include('cms-models::model.partials.form.field_errors', [
            'key'    => $field->key(),
            'errors' => array_get($errors, $locale, []),
        ])
    @endforeach
</div>
