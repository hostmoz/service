# Spondon IT Service

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spondonit/service.svg?style=flat-square)](https://packagist.org/packages/spondonit/service)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

| **Laravel**  |  **service** |
|---|---|
| 7.0  | ^1.0 |

`spondonit/service` is a Laravel package which manage your application installation and update system. This package is supported and tested in Laravel 7.

## Requirements
- [PHP >= 7.2](http://php.net/)
- [Laravel 7|8](https://github.com/laravel/framework)



## Install

To install through Composer, by run the following command:

``` bash
composer require spondonit/service
```

The package will automatically register a service provider and alias.

Optionally, publish the package's configuration file by running:

``` bash
php artisan vendor:publish --tag="spondonit"
```

Need to add this 
```php
   protected $middleware = [
       .....
 \SpondonIt\Service\Middleware\IsInstalled::class, 
   ];
 ```
 Middleware in the $middleware variables on Kernel.php file

 Add ```.version``` and ```.app_installed``` on your ```storage/app``` folder.

 On your ```.version``` file write your app version. And ```.app_installed``` keep it empty


## Credits

- [spondonit](https://spondonit.com)

## About Spondon IT

Spondon IT is a web development company which is specialising on the Laravel framework.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
