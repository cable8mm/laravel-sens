<?php

namespace Seungmun\Sens\Tests;

use NotificationChannels\AwsSns\SnsChannel;
use Seungmun\Sens\Sms\SmsChannel;
use Seungmun\Sens\Support\SmsChannelResolver;

class SmsChannelResolverTest extends TestCase
{
    public function test_it_routes_ncp_countries_to_the_sens_channel(): void
    {
        $resolver = new SmsChannelResolver;

        $this->assertSame([SmsChannel::class], $resolver->resolve('KR'));
        $this->assertSame([SmsChannel::class], $resolver->resolve('US'));
        $this->assertSame([SmsChannel::class], $resolver->resolve('JP'));
        $this->assertTrue($resolver->isNcpSupported('KR'));
    }

    public function test_it_routes_aws_countries_to_the_sns_channel(): void
    {
        $resolver = new SmsChannelResolver;

        $this->assertSame([SnsChannel::class], $resolver->resolve('ST'));
        $this->assertSame([SnsChannel::class], $resolver->resolve('AT'));
        $this->assertTrue($resolver->isAwsSupported('ST'));
        $this->assertFalse($resolver->isAwsSupported('KR'));
    }

    public function test_ncp_wins_over_aws_when_a_country_is_in_both_lists(): void
    {
        // 'KR' is present in both the NCP and AWS default lists.
        // The NCP list takes precedence.
        $resolver = new SmsChannelResolver;

        $this->assertSame([SmsChannel::class], $resolver->resolve('KR'));
    }

    public function test_it_returns_an_empty_list_for_blocked_countries(): void
    {
        $resolver = new SmsChannelResolver;

        $this->assertSame([], $resolver->resolve('MG'));
        $this->assertSame([], $resolver->resolve('GN'));
        $this->assertTrue($resolver->isBlocked('MG'));
    }

    public function test_blocked_countries_win_over_every_other_list(): void
    {
        // 'ER', 'GH', 'UZ', 'NG' etc. exist in the AWS list but are blocked.
        $resolver = new SmsChannelResolver;

        $this->assertSame('blocked', $resolver->provider('ER'));
        $this->assertSame('blocked', $resolver->provider('NG'));
        $this->assertSame([], $resolver->resolve('ER'));
    }

    public function test_it_falls_back_to_vonage_for_unknown_countries(): void
    {
        $resolver = new SmsChannelResolver;

        $this->assertSame(['vonage'], $resolver->resolve('XX'));
        $this->assertSame(['vonage'], $resolver->resolve('ZZ'));
        $this->assertSame('fallback', $resolver->provider('XX'));
    }

    public function test_it_normalizes_the_country_code(): void
    {
        $resolver = new SmsChannelResolver;

        $this->assertSame([SmsChannel::class], $resolver->resolve('kr'));
        $this->assertSame([SmsChannel::class], $resolver->resolve(' kr '));
    }

    public function test_it_allows_per_key_overrides_through_the_config(): void
    {
        config()->set('laravel-sens.routing', [
            'ncp_countries' => ['ST'],
            'channels' => ['fallback' => 'test-fallback'],
        ]);

        $resolver = $this->app->make(SmsChannelResolver::class);

        // Overridden NCP list: ST now routes to SENS.
        $this->assertSame([SmsChannel::class], $resolver->resolve('ST'));
        // KR is no longer in the NCP list, but the AWS default list contains KR.
        $this->assertSame([SnsChannel::class], $resolver->resolve('KR'));
        // Fallback channel is overridden.
        $this->assertSame(['test-fallback'], $resolver->resolve('XX'));
        // Blocked list falls back to the default.
        $this->assertSame([], $resolver->resolve('MG'));
    }

    public function test_it_allows_a_fully_custom_country_list(): void
    {
        config()->set('laravel-sens.routing', [
            'ncp_countries' => ['US'],
            'aws_countries' => [],
            'blocked_countries' => [],
        ]);

        $resolver = $this->app->make(SmsChannelResolver::class);

        $this->assertSame([SmsChannel::class], $resolver->resolve('US'));
        // aws_countries is overridden to an empty list, so ST falls back to vonage.
        $this->assertSame(['vonage'], $resolver->resolve('ST'));
        // blocked_countries is overridden to an empty list, so MG is no longer blocked.
        $this->assertSame(['vonage'], $resolver->resolve('MG'));
    }

    public function test_it_registers_a_singleton_in_the_container(): void
    {
        $this->assertSame(
            $this->app->make(SmsChannelResolver::class),
            $this->app->make(SmsChannelResolver::class)
        );
    }
}
