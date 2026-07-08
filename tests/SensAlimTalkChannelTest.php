<?php

namespace Seungmun\Sens\Tests;

use Illuminate\Notifications\Notification;
use Mockery as m;
use Mockery\MockInterface;
use Seungmun\Sens\AlimTalk\AlimTalk;
use Seungmun\Sens\AlimTalk\AlimTalkChannel;
use Seungmun\Sens\AlimTalk\AlimTalkMessage;

class SensAlimTalkChannelTest extends TestCase
{
    /**
     * @var MockInterface|AlimTalk
     */
    private $alimtalk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->alimtalk = m::mock(AlimTalk::class);
    }

    public function test_it_sends_the_alimtalk_notification_message(): void
    {
        $payload = [
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
        ];

        $message = m::mock(AlimTalkMessage::class);
        $message->shouldReceive('toArray')
            ->once()
            ->andReturn($payload);

        $notifiable = new class
        {
            public string $phone = '01012345678';
        };

        $notification = new class($message) extends Notification
        {
            public function __construct(private AlimTalkMessage $message) {}

            public function toAlimTalk($notifiable): AlimTalkMessage
            {
                return $this->message;
            }
        };

        $this->alimtalk->shouldReceive('send')
            ->once()
            ->with($payload);

        (new AlimTalkChannel($this->alimtalk))->send($notifiable, $notification);

        $this->addToAssertionCount(1);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
