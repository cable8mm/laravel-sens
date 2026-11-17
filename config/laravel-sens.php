<?php

use Seungmun\Sens\Sms\SmsChannel;

return [

    /*
    |--------------------------------------------------------------------------
    | NCLOUD SENS Service ID for SMS or LMS
    |--------------------------------------------------------------------------
    |
    | Service ID used to authenticate the SENS api request.
    |
    */
    'service_id' => env('SENS_SERVICE_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | NCLOUD SENS Service ID for AlimTalk
    |--------------------------------------------------------------------------
    |
    | Service ID used to authenticate the SENS AlimTalk api request.
    | SMS service ID is not same with this AlimTalk service ID.
    */
    'alimtalk_service_id' => env('SENS_ALIMTALK_SERVICE_ID', ''),
    'plus_friend_id' => env('SENS_PLUS_FRIEND_ID', '@id'),

    /*
    |--------------------------------------------------------------------------
    | SMS channel routing by country
    |--------------------------------------------------------------------------
    |
    | Choose the SMS notification channel for a phone number based on the
    | ISO 3166-1 alpha-2 country code. First match wins:
    |
    |  1. blocked_countries -> message is not sent (empty channel list)
    |  2. ncp_countries     -> [Seungmun\Sens\Sms\SmsChannel::class]
    |  3. aws_countries     -> [NotificationChannels\AwsSns\SnsChannel::class]
    |  4. anything else     -> [channels.fallback] (default 'vonage')
    |
    | Every key is optional. Leave a key as null to use the built-in default
    | list (see Seungmun\Sens\Support\SmsChannelResolver for the defaults and
    | the reference URLs for the provider delivery countries).
    |
    | The AWS SNS and Vonage channels are optional dependencies. If you route
    | to them, install the matching package in your application:
    |
    |   composer require laravel-notification-channels/aws-sns
    |   composer require laravel/vonage-notification-channel
    |
    */
    'routing' => [
        'blocked_countries' => null,
        'ncp_countries' => null,
        'aws_countries' => null,

        'channels' => [
            'ncp' => SmsChannel::class,
            'aws' => 'NotificationChannels\\AwsSns\\SnsChannel',
            'fallback' => 'vonage',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | NCLOUD SENS Access Key
    |--------------------------------------------------------------------------
    |
    | Access key used to authenticate the SENS api request.
    |
    */
    'access_key' => env('SENS_ACCESS_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | NCLOUD SENS Secret Key
    |--------------------------------------------------------------------------
    |
    | Secret key used to authenticate the SENS api request.
    |
    */
    'secret_key' => env('SENS_SECRET_KEY', ''),

];
