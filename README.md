# Optical Network Manager Toolkit (ONMT)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/paulo-hortelan/onmt.svg?style=flat-square)](https://packagist.org/packages/paulo-hortelan/onmt)
[![Tests](https://img.shields.io/github/actions/workflow/status/paulo-hortelan/onmt/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/paulo-hortelan/onmt/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Style](https://img.shields.io/github/actions/workflow/status/paulo-hortelan/onmt/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/paulo-hortelan/onmt/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/paulo-hortelan/onmt.svg?style=flat-square)](https://packagist.org/packages/paulo-hortelan/onmt)
[![Ko-fi](https://img.shields.io/badge/Ko--fi-Buy%20me%20a%20coffee!-%2346b798.svg)](https://ko-fi.com/paulohortelan)

ONMT is a Laravel package for automating OLT and ONT operations over Telnet and TL1, with a unified API across vendors.

## Features

- Unified facades for `Fiberhome`, `Nokia`, `ZTE`, and `Datacom`
- Connection helpers for Telnet and TL1 workflows
- High-level methods for optical data, provisioning, reboot, and cleanup
- Command recording with metadata and persistence support
- Designed for real-world ISP/FTTH operational flows

## Supported Vendors and Models

| Vendor | Models | Transport |
| --- | --- | --- |
| Fiberhome | AN5516-04, AN5516-06, AN5516-06B | TL1 |
| Nokia | FX16 | Telnet and TL1 |
| ZTE | C300, C600 | Telnet |
| Datacom | DM4612 | Telnet |

## Requirements

- PHP `^8.1`
- Laravel components compatible with `illuminate/contracts ^10.0`

## Installation

```bash
composer require paulo-hortelan/onmt
```

## Publish Config and Migrations

```bash
php artisan vendor:publish --tag=onmt-config
php artisan vendor:publish --tag=onmt-migrations
php artisan migrate
```

## Quick Start

### 1) Connect to an OLT

```php
use PauloHortelan\Onmt\Facades\ZTE;

$zte = ZTE::connectTelnet('10.0.0.10', 'username', 'password', 23);
```

### 2) Select targets and run commands

```php
$zte->interfaces(['1/1/1:1']);

$optical = $zte->ontsOpticalPower();
$detail = $zte->detailOntsInfo();
```

### 3) Disconnect

```php
$zte->disconnect();
```

## Example: Fiberhome by Serial

```php
use PauloHortelan\Onmt\Facades\Fiberhome;

$fiberhome = Fiberhome::connectTL1('10.0.0.20', 'username', 'password', 3337, '10.0.0.20');

$fiberhome->serials(['ALCL12345678', 'CMSZ12345678']);
$result = $fiberhome->ontsOpticalPower('NA-NA-1-1');

$fiberhome->disconnect();
```

## Command Recording

```php
$zte->startRecordingCommands(
    description: 'Night maintenance - optical check',
    ponInterface: '1/1/1'
);

$zte->interfaces(['1/1/1:1'])->ontsOpticalPower();

$batch = $zte->stopRecordingCommands();
```

## Configuration

After publishing config, you can define a default operator:

```env
ONMT_DEFAULT_OPERATOR=network-noc
```

## Development

```bash
composer test
composer analyse
composer format
composer lint
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Credits

- [Paulo Hortelan](https://github.com/paulo-hortelan)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
