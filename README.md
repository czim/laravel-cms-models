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

[link-contributors]: ../../contributors
