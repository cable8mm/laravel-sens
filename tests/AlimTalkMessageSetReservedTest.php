<?php

namespace Seungmun\Sens\Tests;

use Seungmun\Sens\AlimTalk\AlimTalkMessage;

class AlimTalkMessageSetReservedTest extends TestCase
{
    public function test_it_sets_reserved_time_and_timezone(): void
    {
        $message = (new AlimTalkMessage)
            ->templateCode('TEMPLATE001')
            ->to('01012345678')
            ->content('Test message')
            ->setReserved('2026-05-31 14:20', 'Asia/Seoul');

        $array = $message->toArray();

        $this->assertSame('2026-05-31 14:20', $array['reserveTime']);
        $this->assertSame('Asia/Seoul', $array['reserveTimeZone']);
    }

    public function test_it_uses_default_timezone_when_not_provided(): void
    {
        $message = (new AlimTalkMessage)
            ->templateCode('TEMPLATE001')
            ->to('01012345678')
            ->content('Test message')
            ->setReserved('2026-05-31 14:20');

        $array = $message->toArray();

        $this->assertSame('2026-05-31 14:20', $array['reserveTime']);
        $this->assertSame('Asia/Seoul', $array['reserveTimeZone']);
    }

    public function test_it_does_not_include_reserve_time_when_not_set(): void
    {
        $message = (new AlimTalkMessage)
            ->templateCode('TEMPLATE001')
            ->to('01012345678')
            ->content('Test message');

        $array = $message->toArray();

        $this->assertArrayNotHasKey('reserveTime', $array);
        $this->assertArrayNotHasKey('reserveTimeZone', $array);
    }
}
