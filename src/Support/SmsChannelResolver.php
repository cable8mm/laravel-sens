<?php

namespace Seungmun\Sens\Support;

use Seungmun\Sens\Sms\SmsChannel;

/**
 * Resolve the notification channel(s) for an SMS delivery based on an
 * ISO 3166-1 alpha-2 country code.
 *
 * Delivery policy (first match wins):
 *  1. Countries in `blocked_countries`    -> [] (message is not sent)
 *  2. Countries in `ncp_countries`        -> [SmsChannel::class] (NCP SENS)
 *  3. Countries in `aws_countries`        -> [SnsChannel::class] (AWS SNS)
 *  4. Anything else                       -> [$fallbackChannel] (default 'vonage')
 *
 * The default country lists reflect the delivery capabilities documented by
 * the original providers at the time of writing (see the config file for
 * reference URLs). All lists can be replaced per key through the
 * `laravel-sens.routing` configuration. Missing keys fall back to the
 * built-in defaults.
 *
 * The returned value is ready to be used in a notification's via() method:
 *
 *     public function via($notifiable): array
 *     {
 *         return app(SmsChannelResolver::class)->resolve($notifiable->tel_verify_iso2);
 *     }
 *
 * The AWS SNS and Vonage channels are optional dependencies of this package.
 * If you route to them, require the matching package in your application:
 *
 *     composer require laravel-notification-channels/aws-sns
 *     composer require laravel/vonage-notification-channel
 *
 * @see https://guide.ncloud-docs.com/docs/sens-smspolicy (NCP SENS delivery countries)
 * @see https://docs.aws.amazon.com/sns/latest/dg/sns-supported-regions-countries.html (AWS SNS delivery countries)
 */
class SmsChannelResolver
{
    public const NCP = 'ncp';

    public const AWS = 'aws';

    public const FALLBACK = 'fallback';

    public const BLOCKED = 'blocked';

    /**
     * Countries where NCP SENS can deliver SMS.
     */
    private const DEFAULT_NCP_COUNTRIES = [
        'KR', 'US', 'BE', 'BY', 'CA', 'CN', 'EG', 'HU', 'IN', 'JO', 'JP',
        'KW', 'MO', 'MK', 'MY', 'NZ', 'PH', 'RU', 'TH', 'TR', 'TM',
    ];

    /**
     * Countries where AWS SNS can deliver SMS.
     */
    private const DEFAULT_AWS_COUNTRIES = [
        'AF', 'AL', 'DZ', 'AD', 'AI', 'AG', 'AM', 'AW', 'AU', 'AT', 'AZ',
        'BH', 'BD', 'BB', 'BZ', 'BM', 'BT', 'BO', 'BA', 'BW', 'BN', 'BG',
        'BF', 'BL', 'KH', 'CM', 'CV', 'CF', 'TD', 'CO', 'KM', 'CK', 'HR',
        'CY', 'CZ', 'CD', 'DK', 'DJ', 'DN', 'GQ', 'ER', 'EE', 'ET', 'FO',
        'FJ', 'FI', 'FR', 'GF', 'PF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GL',
        'GR', 'GD', 'GP', 'GG', 'GN', 'GW', 'GY', 'HN', 'HK', 'IS', 'ID',
        'IQ', 'IE', 'IM', 'IL', 'IT', 'CL', 'JM', 'JE', 'KZ', 'KE', 'XK',
        'KG', 'LA', 'LV', 'LB', 'LS', 'LY', 'LI', 'LT', 'LU', 'MV', 'ML',
        'MT', 'MQ', 'MR', 'MU', 'YT', 'MD', 'MN', 'ME', 'MA', 'MS', 'MM',
        'NA', 'NP', 'NL', 'AN', 'NC', 'NE', 'NG', 'NU', 'NO', 'OM', 'PK',
        'PS', 'PA', 'PG', 'PL', 'PT', 'RE', 'RO', 'RW', 'WS', 'SM', 'ST',
        'SN', 'RS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'KR', 'SS',
        'ES', 'SR', 'SZ', 'SE', 'CH', 'TW', 'TJ', 'TX', 'TL', 'TG', 'TO',
        'TT', 'TN', 'TC', 'UG', 'UA', 'GB', 'UZ', 'VU', 'VG', 'YE', 'ZM',
        'ZW',
    ];

    /**
     * Countries where SMS delivery is blocked entirely.
     */
    private const DEFAULT_BLOCKED_COUNTRIES = [
        'MG', 'LK', 'ER', 'PK', 'GH', 'UA', 'UZ', 'NG', 'AZ', 'DZ',
        'UG', 'NP', 'KG', 'TJ', 'OM', 'ZM', 'YE', 'AF', 'GE', 'ZW',
        'GN',
    ];

    /**
     * @var array<int, string>
     */
    private array $ncpCountries;

    /**
     * @var array<int, string>
     */
    private array $awsCountries;

    /**
     * @var array<int, string>
     */
    private array $blockedCountries;

    private string $ncpChannel;

    private string $awsChannel;

    private string $fallbackChannel;

    /**
     * Create a new resolver instance.
     *
     * @param  array<string, mixed>|null  $routing  Routing configuration. Defaults to
     *                                              the `laravel-sens.routing` configuration when null.
     *                                              Any missing key falls back to the built-in default.
     */
    public function __construct(?array $routing = null)
    {
        if ($routing === null) {
            $routing = function_exists('config') ? (array) config('laravel-sens.routing', []) : [];
        }

        $this->ncpCountries = $this->normalizeList($routing['ncp_countries'] ?? self::DEFAULT_NCP_COUNTRIES);
        $this->awsCountries = $this->normalizeList($routing['aws_countries'] ?? self::DEFAULT_AWS_COUNTRIES);
        $this->blockedCountries = $this->normalizeList($routing['blocked_countries'] ?? self::DEFAULT_BLOCKED_COUNTRIES);

        $channels = (array) ($routing['channels'] ?? []);

        $this->ncpChannel = $channels['ncp'] ?? SmsChannel::class;
        $this->awsChannel = $channels['aws'] ?? 'NotificationChannels\\AwsSns\\SnsChannel';
        $this->fallbackChannel = $channels['fallback'] ?? 'vonage';
    }

    /**
     * Resolve the channel(s) to use for the given ISO 3166-1 alpha-2 country code.
     *
     * @param  string  $iso2  Two-letter country code. Case-insensitive.
     * @return array<int, class-string|string> Channel list ready for Notification::via().
     *                                         An empty array means the message must not be sent.
     */
    public function resolve(string $iso2): array
    {
        return match ($this->provider($iso2)) {
            self::BLOCKED => [],
            self::NCP => [$this->ncpChannel],
            self::AWS => [$this->awsChannel],
            default => [$this->fallbackChannel],
        };
    }

    /**
     * Get the provider key ('blocked', 'ncp', 'aws' or 'fallback') for the given country code.
     */
    public function provider(string $iso2): string
    {
        $iso2 = $this->normalize($iso2);

        if (in_array($iso2, $this->blockedCountries, true)) {
            return self::BLOCKED;
        }

        if (in_array($iso2, $this->ncpCountries, true)) {
            return self::NCP;
        }

        if (in_array($iso2, $this->awsCountries, true)) {
            return self::AWS;
        }

        return self::FALLBACK;
    }

    public function isBlocked(string $iso2): bool
    {
        return $this->provider($iso2) === self::BLOCKED;
    }

    public function isNcpSupported(string $iso2): bool
    {
        return $this->provider($iso2) === self::NCP;
    }

    public function isAwsSupported(string $iso2): bool
    {
        return $this->provider($iso2) === self::AWS;
    }

    /**
     * Normalize a two-letter country code.
     */
    private function normalize(string $iso2): string
    {
        return strtoupper(trim($iso2));
    }

    /**
     * @param  array<int, string>|null  $list
     * @return array<int, string>
     */
    private function normalizeList(?array $list): array
    {
        return array_values(array_unique(array_map(
            fn (string $code): string => strtoupper(trim($code)),
            $list ?? []
        )));
    }
}
