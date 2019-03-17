[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-cms-models.svg?branch=master)](https://travis-ci.org/czim/laravel-cms-models)
[![Coverage Status](https://coveralls.io/repos/github/czim/laravel-cms-models/badge.svg?branch=master)](https://coveralls.io/github/czim/laravel-cms-models?branch=master)


# CMS for Laravel - Main Models Module

This module offers the means to edit Eloquent models through the Laravel CMS.

Features:

- Model listings
- Edit forms for creating and updating model records
- Elaborate (optional) module configuration and customization.
    - Customizable listing columns, scopes, filters, sorting, etc.
    - Customizable edit form html, fields, validation rules, create vs. edit forms, etc.
    - Customizable model references, display labels, etc.
- Support for Translatable models
- Support for Listify'd models, with drag/drop reordering
- Support for nested child-parent listings and breadcrumb trails


To be used to with the [Laravel CMS Core](https://github.com/czim/laravel-cms-core).

For a better end-user experience when using file uploads, installing the [Laravel CMS Upload Module](https://github.com/czim/laravel-cms-upload-module) is recommended.


## Version Compatibility

 Laravel             | Package 
:--------------------|:--------
 5.3                 | 1.3
 5.4                 | 1.4
 5.5                 | 1.5
 5.6                 | 1.6
 5.7                 | 1.7

## Changelog

[View the changelog](CHANGELOG.md).

## Installation

Add the module class to your `cms-modules.php` configuration file:

``` php
    'modules' => [
        // ...
        Czim\CmsModels\Modules\ModelModuleGenerator::class,
    ],
```

Add the service provider to your `cms-modules.php` configuration file:

``` php
    'providers' => [
        // ...
        Czim\CmsModels\Providers\CmsModelsServiceProvider::class,
        // ...
    ],
```

To publish the config:

``` bash
php artisan vendor:publish
```

## Configuration

Models may be added with basic functionality by simply registering them in the `cms-models` config, or special CMS model configuration files may be created for them to customize them as needed.

### Adding and Configuring Models

To register models with the CMS module, you can either:

- Add their full class name to the `cms-models.models` config array:

    ```php
    <?php
          'models' => [
              App\Models\YourModel::class,
          ],
    ```

- Create a model configuration file for them in the configured directory.  
    By default, this is in the `app/Cms/Models/` directory;
    this is defined under the `cms-models.collector.source.dir` key.  

For more details about customizing how models are configured, see [the documentation on Model Configuration](documentation/ModelConfiguration.md).


## Troubleshooting

If you run into problems, please consult the [CMS core troubleshooting section](https://github.com/czim/laravel-cms-core/blob/master/documentation/Troubleshooting.md).

## Further information

- [General information](documentation/General.md) about the CMS
- [Model Configuration](documentation/ModelConfiguration.md)
- [Strategies](documentation/Strategies.md) for all kinds of CMS setup and interaction 

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-cms-models.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-cms-models.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-cms-models
[link-downloads]: https://packagist.org/packages/czim/laravel-cms-models
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
