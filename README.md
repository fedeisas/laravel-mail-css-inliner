Laravel Mail CSS Inliner
========================

[![Travis Badge](https://secure.travis-ci.org/fedeisas/laravel-mail-css-inliner.png)](http://travis-ci.org/fedeisas/laravel-mail-css-inliner)
[![Coverage Status](https://coveralls.io/repos/fedeisas/laravel-mail-css-inliner/badge.png)](https://coveralls.io/r/fedeisas/laravel-mail-css-inliner)
[![Latest Stable Version](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/v/stable.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![Latest Unstable Version](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/v/unstable.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![Total Downloads](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/downloads.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![License](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/license.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)

## Why?
Most email clients won't render CSS (on a `<link>` or a `<style>`). The solution is inline your CSS directly on the HTML. Doing this by hand easily turns into unmantainable templates.
The goal of this package is to automate the process of inlining that CSS before sending the emails.

## How?
Using a wonderful [CSS inliner package](https://github.com/tijsverkoyen/CssToInlineStyles) wraped in a SwiftMailer plugin and served as a Service Provider it justs works without any configuration.

Turns:
```html
<html>
    <head>
        <style>
            h1 {
                font-size: 24px;
                color: #000;
            }
        </style>
    </head>
    <body>
        <h1>Hey you</h1>
    </body>
</html>
```

Into this:
```html
<html>
    <head>
    </head>
    <body>
        <h1 style="font-size: 24px; color: #000;">Hey you</h1>
    </body>
</html>
```

## Installation
Begin by installing this package through Composer. Edit your project's `composer.json` file to require `fedeisas/laravel-mail-css-inliner`.

This package needs Laravel 5.x
```json
{
  "require": {
        "fedeisas/laravel-mail-css-inliner": "~1.4"
    }
}
```

Next, update Composer from the Terminal:
```bash
$ composer update
```

Once this operation completes, you must add the service provider. Open `app/config/app.php`, and add a new item to the providers array.
```php
'Fedeisas\LaravelMailCssInliner\LaravelMailCssInlinerServiceProvider',
```

At this point the inliner should be already working with the default options. If you want to fine-tune these options, you can do so by publishing the configuration file:
```bash
$ php artisan vendor:publish --provider='Fedeisas\\LaravelMailCssInliner\\LaravelMailCssInlinerServiceProvider'
```
and changing the settings on the generated `config/css-inliner.php` file.

## Contributing
```bash
$ composer install
$ ./vendor/bin/phpunit
```
In addition to a full test suite, there is Travis integration.

## Found a bug?
Please, let me know! Send a pull request or a patch. Questions? Ask! I will respond to all filed issues.

## Inspiration
This package is greatly inspired, and mostly copied, from [SwiftMailer CSS Inliner](https://github.com/OpenBuildings/swiftmailer-css-inliner). I just made an easy drop-in solution for Laravel.

## License
This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
