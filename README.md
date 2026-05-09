# Laravel NCLOUD SENS Notification Channel

[![Latest Stable Version](https://poser.pugx.org/cable8mm/laravel-sens/v)](https://packagist.org/packages/cable8mm/laravel-sens)
[![Total Downloads](https://poser.pugx.org/cable8mm/laravel-sens/downloads)](https://packagist.org/packages/cable8mm/laravel-sens)
[![License](https://poser.pugx.org/cable8mm/laravel-sens/license)](https://packagist.org/packages/cable8mm/laravel-sens)
[![Tests](https://github.com/cable8mm/laravel-sens/actions/workflows/tests.yml/badge.svg)](https://github.com/cable8mm/laravel-sens/actions/workflows/tests.yml)

Laravel notification channels for [NAVER Cloud Platform SENS](https://www.ncloud.com/product/applicationService/sens).

This package is a maintained fork of [`seungmun/laravel-sens`](https://github.com/seungmun/laravel-sens). The original package provided the foundation for sending SMS, MMS, and Kakao AlimTalk notifications through NCLOUD SENS. This fork keeps the package usable with current PHP and Laravel versions while preserving the existing public API as much as possible.

## Features

- SMS and LMS notifications
- MMS notifications with file attachment support
- Kakao AlimTalk notifications
- Laravel notification channel integration
- Auto-discovery service provider

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11, 12, or 13
- NCLOUD SENS credentials and service IDs

## Installation

Install the package with Composer:

```bash
composer require cable8mm/laravel-sens
```

Laravel will automatically discover the service provider.

Publish the configuration file when you want to customize it:

```bash
php artisan vendor:publish --provider="Seungmun\Sens\SensServiceProvider" --tag="config"
```

## Configuration

Add your NCLOUD SENS credentials to `.env`:

```env
SENS_ACCESS_KEY=your-sens-access-key
SENS_SECRET_KEY=your-sens-secret-key
SENS_SERVICE_ID=your-sms-service-id
SENS_ALIMTALK_SERVICE_ID=your-alimtalk-service-id
SENS_PlUS_FRIEND_ID=@your-plus-friend-id
```

The `SENS_PlUS_FRIEND_ID` key keeps the spelling used by the original package configuration.

If you publish the configuration, it will be available at `config/laravel-sens.php`.

## Usage

Use this package through Laravel's built-in notification system.

### Sending SMS

Create a notification:

```bash
php artisan make:notification SendPurchaseReceipt
```

Return `SmsChannel::class` from `via()` and build an `SmsMessage` from `toSms()`:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Seungmun\Sens\Sms\SmsChannel;
use Seungmun\Sens\Sms\SmsMessage;

class SendPurchaseReceipt extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }

    public function toSms($notifiable): SmsMessage
    {
        return (new SmsMessage())
            ->to($notifiable->phone)
            ->from('055-000-0000')
            ->content('Your purchase receipt is ready.')
            ->contentType('COMM')
            ->type('SMS');
    }
}
```

Send the notification as usual:

```php
use App\Models\User;
use App\Notifications\SendPurchaseReceipt;

User::find(1)->notify(new SendPurchaseReceipt());
```

### Sending MMS

Use `type('MMS')` and attach a file path or an `Illuminate\Http\UploadedFile` instance:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notification;
use Seungmun\Sens\Sms\SmsChannel;
use Seungmun\Sens\Sms\SmsMessage;

class SendPurchaseInvoice extends Notification
{
    use Queueable;

    public function __construct(private UploadedFile $image)
    {
    }

    public function via($notifiable): array
    {
        return [SmsChannel::class];
    }

    /**
     * @throws FileNotFoundException
     */
    public function toSms($notifiable): SmsMessage
    {
        return (new SmsMessage())
            ->type('MMS')
            ->to($notifiable->phone)
            ->from('055-000-0000')
            ->content("This is your invoice.\nCheck out the attached image.")
            ->file('invoice.jpg', $this->image);
    }
}
```

```php
use App\Models\User;
use App\Notifications\SendPurchaseInvoice;

User::find(1)->notify(new SendPurchaseInvoice(request()->file('image')));
```

### Sending AlimTalk

Return `AlimTalkChannel::class` from `via()` and build an `AlimTalkMessage` from `toAlimTalk()`:

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Seungmun\Sens\AlimTalk\AlimTalkChannel;
use Seungmun\Sens\AlimTalk\AlimTalkMessage;

class SendShippingNotice extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return [AlimTalkChannel::class];
    }

    public function toAlimTalk($notifiable): AlimTalkMessage
    {
        return (new AlimTalkMessage())
            ->templateCode('TEMPLATE001')
            ->to($notifiable->phone)
            ->content('Your order has been shipped.')
            ->countryCode('82')
            ->addButton(['type' => 'DS', 'name' => 'Tracking of Shipment'])
            ->setReserved('2026-05-31 14:20', 'Asia/Seoul');
    }
}
```

## Compatibility Notes

The Composer package name is `cable8mm/laravel-sens`, but the PHP namespace remains `Seungmun\Sens` for backward compatibility with the original package.

If you are migrating from `seungmun/laravel-sens`, replace the Composer package and keep your existing notification code:

```bash
composer remove seungmun/laravel-sens
composer require cable8mm/laravel-sens
```

## Contributing

Issues and pull requests are welcome. Please keep changes focused, include tests for behavior changes, and run the test suite before opening a pull request:

```bash
composer test
composer lint
```

## Credits

- [Seungmun Jeong](https://github.com/seungmun), original author
- [Samgu Lee](https://github.com/cable8mm), fork maintainer
- All contributors to the original and forked packages

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
