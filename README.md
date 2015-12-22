# Laravel ACL

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Simple Role Permission ACL for Laravel Framework.

## Install

Via Composer

``` bash
$ composer require yajra/laravel-acl
```

## Usage
Register service provider:
``` php
Yajra\Acl\AclServiceProvider::class
```

Publish assets:
```php
$ php artisan vendor:publish --tag=laravel-acl
```

This will publish `acl.php` config file and migrations files.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email aqangeles@gmail.com instead of using the issue tracker.

## Credits

- [Arjay Angeles][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/yajra/acl.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yajra/acl/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/yajra/acl.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/yajra/acl.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/yajra/acl.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yajra/acl
[link-travis]: https://travis-ci.org/yajra/acl
[link-scrutinizer]: https://scrutinizer-ci.com/g/yajra/acl/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/yajra/acl
[link-downloads]: https://packagist.org/packages/yajra/acl
[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
