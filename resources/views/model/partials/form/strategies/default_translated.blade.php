
<div class="translated-form-field-container">

    <?php
        // todo fix this to be variable other than through entire locale
        $currentLocale = app()->getLocale();
    ?>

    {{-- for each active locale show the pre-rendered form field strategy view --}}
    @foreach ($locales as $locale)

        <div class="translated-form-field-wrapper"
             data-locale="{{ $locale }}"
             @if($currentLocale != $locale) style="display: none" @endif
        >
            {!! $localeRendered[ $locale ] !!}
        </div>

    @endforeach


    {{-- locale switcher --}}
    <div class="dropdown translated-form-field-locale-select">
        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
            <img src="{{ asset("_cms/img/flags/{$currentLocale}.png") }}" title="{{ $currentLocale }}">
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            @foreach ($locales as $locale)

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

</div>
