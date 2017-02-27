
<textarea id="field-{{ $key }}"
    name="{{ $name ?: $key }}"
    class="form-control wysiwyg"
    rows="{{ array_get($options, 'rows') }}"
    cols="{{ array_get($options, 'cols') }}"
    @if ($required && ! $translated) required="required" @endif
>{{ $value }}</textarea>

@include('cms-models::model.partials.form.field_errors', [
    'key'        => isset($baseKey) ? $baseKey : $key,
    'errors'     => $errors,
    'translated' => $translated,
])


@push('javascript-end')
    <!-- form field display strategy: wysiwyg -->
    <script>
        $(function() {

            <?php
                $settings = [];

                // Build config path to use
                $configPath = array_get(
                    $options,
                    'config',
                    config('cms-models.ckeditor.config')
                );

                if ($configPath) {
                    if ( ! ends_with($configPath, '.js')) {
                        $configPath .= '.js';
                    }

                    $configPath = ltrim($configPath, '/');

                    // Check whether config file exists, otherwise we're going to try and look it up in the default
                    // ckeditor config directory
                    if ( ! file_exists(public_path($configPath))) {
                        $configPath = '/' . trim(config('cms-models.ckeditor.path'), '/') . '/' . $configPath;
                    } else {
                        // Set the config path to the absolute url, because otherwise CKEditor will still try to
                        // load the file relatively from the CKEditor directory
                        $configPath = url($configPath);
                    }

                    $settings['customConfig'] = $configPath;
                }

                // Determine whether the toolbar should be collapsed by default
                if (array_get($options, 'collapse_toolbar')) {
                    $settings['toolbarStartupExpanded'] = false;
                }
            ?>

            @if ( ! count($settings))
                CKEDITOR.replace("field-{{ $key }}");
            @else
                CKEDITOR.replace("field-{{ $key }}", {!! json_encode($settings) !!});
            @endif
        });
    </script>
@endpush
