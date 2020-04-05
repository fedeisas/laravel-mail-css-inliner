Laravel Mail CSS Inliner
========================

![](https://github.com/fedeisas/laravel-mail-css-inliner/workflows/CI/badge.svg)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=fedeisas/laravel-mail-css-inliner&identifier=16568832)](https://dependabot.com)
[![Latest Stable Version](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/v/stable.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![Latest Unstable Version](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/v/unstable.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![Total Downloads](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/downloads.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)
[![License](https://poser.pugx.org/fedeisas/laravel-mail-css-inliner/license.png)](https://packagist.org/packages/fedeisas/laravel-mail-css-inliner)

## Why?
Most email clients won't render CSS (on a `<link>` or a `<style>`). The solution is inline your CSS directly on the HTML. Doing this by hand easily turns into unmantainable templates.
The goal of this package is to automate the process of inlining that CSS before sending the emails.

## How?
Using a wonderful [CSS inliner package](https://github.com/tijsverkoyen/CssToInlineStyles) wrapped in a SwiftMailer plugin and served as a Service Provider it just works without any configuration.
Since this is a SwiftMailer plugin, it will automatically inline your css when parsing an email template. You don't have to do anything!

Turns style tag:
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
Or the link tag:
```html
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="./tests/css/test.css">
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
        <style>
            h1 {
                font-size: 24px;
                color: #000;
            }
        </style>
    </head>
    <body>
        <h1 style="font-size: 24px; color: #000;">Hey you</h1>
    </body>
</html>
```

## Installation
This package needs Laravel 7.x.

Begin by installing this package through Composer. Require it directly from the Terminal to take the last stable version:
```bash
$ composer require fedeisas/laravel-mail-css-inliner
```

At this point the inliner should be already working with the default options. If you want to fine-tune these options, you can do so by publishing the configuration file:
```bash
$ php artisan vendor:publish --provider='Fedeisas\LaravelMailCssInliner\LaravelMailCssInlinerServiceProvider'
```
and changing the settings on the generated `config/css-inliner.php` file.

## Contributing
```bash
$ composer install
$ ./vendor/bin/phpunit
```

## Found a bug?
Please, let me know! Send a pull request or a patch. Questions? Ask! I will respond to all filed issues.

## Inspiration
This package is greatly inspired, and mostly copied, from [SwiftMailer CSS Inliner](https://github.com/OpenBuildings/swiftmailer-css-inliner). I just made an easy drop-in solution for Laravel.

## License
This package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
