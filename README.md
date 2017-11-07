# Laravel ACL

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Laravel ACL is a simple role, permission ACL for Laravel Framework.
This package was based on the great package [Caffeinated/Shinobi](https://github.com/caffeinated/shinobi) but is fully compatible with Laravel's built-in Gate/Authorization system.

## Documentations
- [Laravel ACL][link-docs]

## Installation

Via Composer

``` bash
$ composer require yajra/laravel-acl:^3.0
```

## Configuration
Register service provider (Optional on Laravel 5.5).
``` php
Yajra\Acl\AclServiceProvider::class
```

Publish assets (Optional):
```php
$ php artisan vendor:publish --tag=laravel-acl
```

Run migrations:
```php
php artisan migrate
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email aqangeles@gmail.com instead of using the issue tracker.

## Credits

- [Arjay Angeles][link-author]
- [Caffeinated/Shinobi](https://github.com/caffeinated/shinobi)
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Buy me a coffee
<a href='https://www.patreon.com/bePatron?u=4521203'><img alt='Become a Patron' src='https://s3.amazonaws.com/patreon_public_assets/toolbox/patreon.png' border='0' width='200px' ></a>

[ico-version]: https://img.shields.io/packagist/v/yajra/laravel-acl.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/yajra/laravel-acl/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/yajra/laravel-acl.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/yajra/laravel-acl.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/yajra/laravel-acl.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yajra/laravel-acl
[link-travis]: https://travis-ci.org/yajra/laravel-acl
[link-scrutinizer]: https://scrutinizer-ci.com/g/yajra/laravel-acl/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/yajra/laravel-acl
[link-downloads]: https://packagist.org/packages/yajra/laravel-acl
[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
[link-docs]: https://yajrabox.com/docs/laravel-acl/3.0
