<?php

namespace Seungmun\Sens\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Seungmun\Sens\Sms\SmsMessage;

class SmsMessageTest extends TestCase
{
    public function test_it_serializes_an_sms_message_for_sens(): void
    {
        $message = (new SmsMessage)
            ->to('010-1234-5678')
            ->from('055-000-0000')
            ->content('Hello from Laravel SENS.')
            ->contentType('ad')
            ->type('lms')
            ->subject('Greeting');

        $this->assertSame([
            'type' => 'LMS',
            'contentType' => 'AD',
            'countryCode' => '82',
            'from' => '0550000000',
            'subject' => 'Greeting',
            'content' => 'Hello from Laravel SENS.',
            'messages' => [
                ['to' => '01012345678'],
            ],
        ], $message->toArray());
    }

    public function test_it_uses_the_configured_sender_as_the_default_from_number(): void
    {
        $this->assertSame('055-000-0000', (new SmsMessage)->from);
    }

    public function test_it_serializes_file_attachments_for_mms(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sens-mms-');

        file_put_contents($path, 'invoice-image');

        try {
            $message = (new SmsMessage)
                ->type('MMS')
                ->to('01012345678')
                ->content('Invoice')
                ->file('invoice.jpg', $path);

            $this->assertSame([
                [
                    'name' => 'invoice.jpg',
                    'body' => base64_encode('invoice-image'),
                ],
            ], $message->toArray()['files']);
        } finally {
            unlink($path);
        }
    }

    public function test_it_rejects_invalid_file_attachments(): void
    {
        $this->expectException(FileNotFoundException::class);

        (new SmsMessage)->file('invoice.jpg', []);
    }
}
