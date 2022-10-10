# Laravel ACL

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
![Build Status](https://github.com/yajra/laravel-acl/workflows/tests/badge.svg)
[![Total Downloads][ico-downloads]][link-downloads]

Laravel ACL (Access Control List) is a simple role-permission ACL for the Laravel Framework.
This package was based on the great package [Caffeinated/Shinobi](https://github.com/caffeinated/shinobi) but is fully compatible with Laravel's built-in Gate/Authorization system.

## Documentations
- [Laravel ACL][link-docs]

## Laravel Version Compatibility

| Laravel       | Package |
|:--------------|:--------|
| 8.x and below | 6.x     |
| 9.x           | 9.x     |

## Installation

Via Composer

``` bash
$ composer require yajra/laravel-acl
```

## Configuration
Register service provider (Optional on Laravel 5.5+).
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
[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.me/yajra)
<a href='https://www.patreon.com/bePatron?u=4521203'><img alt='Become a Patron' src='https://s3.amazonaws.com/patreon_public_assets/toolbox/patreon.png' border='0' width='200px' ></a>

[ico-version]: https://img.shields.io/packagist/v/yajra/laravel-acl.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/yajra/laravel-acl.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/yajra/laravel-acl
[link-downloads]: https://packagist.org/packages/yajra/laravel-acl
[link-author]: https://github.com/yajra
[link-contributors]: ../../contributors
[link-docs]: https://yajrabox.com/docs/laravel-acl/master
