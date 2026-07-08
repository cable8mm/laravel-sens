<?php

namespace Seungmun\Sens\Tests;

use Illuminate\Notifications\Notification;
use Mockery as m;
use Mockery\MockInterface;
use Seungmun\Sens\Sms\Sms;
use Seungmun\Sens\Sms\SmsChannel;
use Seungmun\Sens\Sms\SmsMessage;

class SensSmsChannelTest extends TestCase
{
    /**
     * @var MockInterface|Sms
     */
    private $sms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sms = m::mock(Sms::class);
    }

    public function test_it_sends_the_sms_notification_message(): void
    {
        $payload = [
            'type' => 'SMS',
            'contentType' => 'COMM',
            'countryCode' => '82',
            'from' => '0550000000',
            'subject' => null,
            'content' => 'Your purchase receipt is ready.',
            'messages' => [
                ['to' => '01012345678'],
            ],
        ];

        $message = m::mock(SmsMessage::class);
        $message->shouldReceive('toArray')
            ->once()
            ->andReturn($payload);

        $notifiable = new class
        {
            public string $phone = '01012345678';
        };

        $notification = new class($message) extends Notification
        {
            public function __construct(private SmsMessage $message) {}

            public function toSms($notifiable): SmsMessage
            {
                return $this->message;
            }
        };

        $this->sms->shouldReceive('send')
            ->once()
            ->with($payload);

        (new SmsChannel($this->sms))->send($notifiable, $notification);

        $this->addToAssertionCount(1);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
