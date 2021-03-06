# Changelog

## Laravel 5.7

### [1.7.3] - 2020-08-05

See 1.5.11.

### [1.7.2] - 2019-09-06

Fixed issue with $errors variable in validation view.
When using `czim/laravel-cms-theme` version `0.9.10` and up, you can now change the pagination view partial used.

### [1.7.1] - 2019-09-06

Fixed issue with unset variable in filter view that caused any page with filters to error on some setups.

### [1.7.0] - 2019-03-17

Laravel 5.7 support.

## Laravel 5.6

### [1.6.7] - 2020-08-06

See 1.7.1.

### [1.6.6] - 2020-08-05

See 1.5.11.

### [1.6.5] - 2018-06-20
- Some cleanup and correction of omissions for validation handling during enrichment.
- Added '<field>' placeholder handling for model information and strategy generated validation rule content.
- Fixed validation rule generation for Paperclip strategy (for older versions, this goes for Stapler too).

### [1.6.4] - 2018-06-19
- Rebuilt validation handling to allow for more control over configuring rules, and to solve an issue with nested rules for translated fields (incorrect locale placeholder position).
- Merged fix by @JJWesterkamp for analyzing multiline relation method definitions.

### [1.6.3] - 2018-05-07
Fixed incorrect Blade directive with file upload form display strategy (with upload module).


### [1.6.2] - 2018-04-12
Merged fix for missing form data for unchecked checkbox.


### [1.6.1] - 2018-02-15
Added form field store enum value `PASSWORD_UPDATED`.
Added form display `PasswordStrategy` for `<input type="password">`.
Updated `cms-models.strategies.form` config section.


## Laravel 5.5

### [1.5.11] - 2020-04-02

Solved issue where Select2 returns array with null or empty string, instead of empty array for no linked items.

### [1.5.10] - 2018-06-20
See 1.6.5.

### [1.5.9] - 2018-06-19
See 1.6.4.

### [1.5.8] - 2018-05-08
Fixed incorrect Blade directive for Stapler strategy (by @JJWesterkamp).

### [1.5.7] - 2018-05-07
See 1.6.3.

### [1.5.6] - 2018-04-12
See 1.6.2.

### [1.5.5] - 2018-02-15
See 1.6.1.


## Laravel 5.4

### [1.4.11] - 2018-06-20
See 1.6.5.

### [1.4.10] - 2018-04-12
See 1.6.2.

### [1.4.9] - 2018-02-15
See 1.6.1.


## Laravel 5.3

### [1.3.12] - 2018-06-21
See 1.6.5.

### [1.3.11] - 2018-04-12
See 1.6.2.

### [1.3.10] - 2018-02-15
See 1.6.1.


[1.7.3]: https://github.com/czim/laravel-cms-models/compare/1.7.1...1.7.3
[1.7.1]: https://github.com/czim/laravel-cms-models/compare/1.7.0...1.7.1

[1.7.0]: https://github.com/czim/laravel-cms-models/compare/1.6.5...1.7.0

[1.6.5]: https://github.com/czim/laravel-cms-models/compare/1.6.4...1.6.5
[1.6.4]: https://github.com/czim/laravel-cms-models/compare/1.6.3...1.6.4
[1.6.3]: https://github.com/czim/laravel-cms-models/compare/1.6.2...1.6.3
[1.6.2]: https://github.com/czim/laravel-cms-models/compare/1.6.1...1.6.2
[1.6.1]: https://github.com/czim/laravel-cms-models/compare/1.6.0...1.6.1

[1.5.11]: https://github.com/czim/laravel-cms-models/compare/1.5.10...1.5.11
[1.5.10]: https://github.com/czim/laravel-cms-models/compare/1.5.9...1.5.10
[1.5.9]: https://github.com/czim/laravel-cms-models/compare/1.5.8...1.5.9
[1.5.8]: https://github.com/czim/laravel-cms-models/compare/1.5.7...1.5.8
[1.5.7]: https://github.com/czim/laravel-cms-models/compare/1.5.6...1.5.7
[1.5.6]: https://github.com/czim/laravel-cms-models/compare/1.5.5...1.5.6
[1.5.5]: https://github.com/czim/laravel-cms-models/compare/1.5.4...1.5.5

[1.4.11]: https://github.com/czim/laravel-cms-models/compare/1.4.10...1.4.11
[1.4.10]: https://github.com/czim/laravel-cms-models/compare/1.4.9...1.4.10
[1.4.9]: https://github.com/czim/laravel-cms-models/compare/1.4.8...1.4.9

[1.3.12]: https://github.com/czim/laravel-cms-models/compare/1.3.11...1.3.12
[1.3.11]: https://github.com/czim/laravel-cms-models/compare/1.3.10...1.3.11
[1.3.10]: https://github.com/czim/laravel-cms-models/compare/1.3.9...1.3.10
