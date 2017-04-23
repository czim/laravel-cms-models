<?php

/*
 * This allows manipulating the contents of the model configuration
 * on the fly, using the container.
 */

if (app()->bound('cms-models-test.integration.information.test-post')) {
    return app('cms-models-test.integration.information.test-post');
}

return [

    'list' => [
        'default_sort' => 'id',
    ],

    'single' => false,
];
