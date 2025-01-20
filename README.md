<!-- <p align="center"><img src="/images/requests-graph.png" alt="Requests Graph for Laravel Pulse"></p> -->

# Optical Network Manager (ONMT)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paulo-hortelan/requests-graph-pulse.svg?style=flat-square)](https://packagist.org/packages/paulo-hortelan/onmt)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/paulo-hortelan/onmt/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/paulo-hortelan/onmt/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/paulo-hortelan/onmt/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/paulo-hortelan/onmt/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/paulo-hortelan/onmt.svg?style=flat-square)](https://packagist.org/packages/paulo-hortelan/onmt)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-Buy%20me%20a%20coffee!-%2346b798.svg)](https://ko-fi.com/paulohortelan)

This is a Laravel package that adds methods for interacting and gather information aboout optical devices. 

<!-- - Customizable requests status to be shown -->

## Installation

You can install the package via composer:

```bash
composer require paulo-hortelan/onmt
```

## Publishing

You can publish assets using the following Artisan commands:

```bash
php artisan vendor:publish --tag=onmt-config
php artisan vendor:publish --tag=onmt-migrations
php artisan vendor:publish --tag=onmt-models
```

<!-- ## Register the recorder

Add the `RequestsGraphRecorder` inside `config/pulse.php`. (If you don\'t have this file make sure you have published the config file of Larave Pulse using `php artisan vendor:publish --tag=pulse-config`) -->

<!-- ```
return [
    // ...

    'recorders' => [
        // Existing recorders...

        \PauloHortelan\RequestsGraphPulse\Recorders\RequestsGraphRecorder::class => [
            'enabled' => env('PULSE_REQUESTS_GRAPH_ENABLED', true),
            'sample_rate' => env('PULSE_REQUESTS_GRAPH_SAMPLE_RATE', 1),
            'record_informational' => env('PULSE_REQUESTS_GRAPH_RECORD_INFORMATIONAL', false),
            'record_successful' => env('PULSE_REQUESTS_GRAPH_RECORD_SUCCESSFUL', true),
            'record_redirection' => env('PULSE_REQUESTS_GRAPH_RECORD_REDIRECTION', false),
            'record_client_error' => env('PULSE_REQUESTS_GRAPH_RECORD_CLIENT_ERROR', true),
            'record_server_error' => env('PULSE_REQUESTS_GRAPH_RECORD_SERVER_ERROR', true),
            'ignore' => [
                '#^/pulse$#', // Pulse dashboard...
            ],            
        ], 
    ]
]
``` -->

<!-- ## Add to your dashboard

To add the card to the Pulse dashboard, you must first [publish the vendor view](https://laravel.com/docs/10.x/pulse#dashboard-customization).

```bash
php artisan vendor:publish --tag=pulse-dashboard
```

Then, you can modify the `dashboard.blade.php` file and add the requests-graph livewire template:

```php
<livewire:requests-graph cols="6" />
``` -->

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Paulo Hortelan](https://github.com/paulo-hortelan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
