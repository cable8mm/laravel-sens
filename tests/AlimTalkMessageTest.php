<?php

namespace Seungmun\Sens\Tests;

use Seungmun\Sens\AlimTalk\AlimTalkMessage;

class AlimTalkMessageTest extends TestCase
{
    public function test_it_serializes_an_alimtalk_message_for_sens(): void
    {
        $message = (new AlimTalkMessage)
            ->templateCode('TEMPLATE001')
            ->to('01012345678')
            ->content('Your order has been shipped.')
            ->countryCode('82')
            ->addButton(['type' => 'DS', 'name' => 'Track'])
            ->setReserved('2026-05-31 14:20', 'Asia/Seoul');

        $this->assertSame([
            'plusFriendId' => '@sens',
            'templateCode' => 'TEMPLATE001',
            'scheduleCode' => null,
            'reserveTime' => '2026-05-31 14:20',
            'reserveTimeZone' => 'Asia/Seoul',
            'messages' => [
                [
                    'countryCode' => '82',
                    'to' => '01012345678',
                    'content' => 'Your order has been shipped.',
                    'buttons' => [
                        ['type' => 'DS', 'name' => 'Track'],
                    ],
                ],
            ],
        ], $message->toArray());
    }

    public function test_it_allows_overriding_the_plus_friend_id(): void
    {
        $message = (new AlimTalkMessage('@custom'))
            ->templateCode('TEMPLATE001')
            ->to('01012345678')
            ->content('Hello');

        $this->assertSame('@custom', $message->toArray()['plusFriendId']);
    }
}
