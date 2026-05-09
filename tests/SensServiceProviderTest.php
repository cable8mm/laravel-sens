<?php

namespace Seungmun\Sens\Tests;

use Seungmun\Sens\AlimTalk\AlimTalkChannel;
use Seungmun\Sens\Sms\SmsChannel;

class SensServiceProviderTest extends TestCase
{
    public function test_it_merges_the_package_configuration(): void
    {
        $this->assertSame('sms-service-id', config('laravel-sens.service_id'));
        $this->assertSame('alimtalk-service-id', config('laravel-sens.alimtalk_service_id'));
        $this->assertSame('@sens', config('laravel-sens.plus_friend_id'));
        $this->assertSame('access-key', config('laravel-sens.access_key'));
        $this->assertSame('secret-key', config('laravel-sens.secret_key'));
    }

    public function test_it_resolves_notification_channels_from_the_container(): void
    {
        $this->assertInstanceOf(SmsChannel::class, $this->app->make(SmsChannel::class));
        $this->assertInstanceOf(AlimTalkChannel::class, $this->app->make(AlimTalkChannel::class));
    }
}
