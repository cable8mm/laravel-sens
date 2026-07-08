<?php

namespace Seungmun\Sens\Tests;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Seungmun\Sens\Sms\SmsMessage;

class SmsMessageEdgeCaseTest extends TestCase
{
    public function test_it_handles_empty_content(): void
    {
        $message = (new SmsMessage)
            ->to('01012345678')
            ->from('0550000000');

        $array = $message->toArray();

        $this->assertSame('', $array['content']);
    }

    public function test_it_does_not_include_subject_when_null(): void
    {
        $message = (new SmsMessage)
            ->to('01012345678')
            ->from('0550000000')
            ->content('Test');

        $array = $message->toArray();

        $this->assertArrayNotHasKey('subject', $array);
    }

    public function test_it_includes_subject_when_provided(): void
    {
        $message = (new SmsMessage)
            ->to('01012345678')
            ->from('0550000000')
            ->content('Test')
            ->subject('Subject');

        $array = $message->toArray();

        $this->assertSame('Subject', $array['subject']);
    }

    public function test_it_rejects_file_exceeding_size_limit(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File size exceeds 1MB limit.');

        // Create a file larger than 1MB
        $largeContent = str_repeat('a', 1024 * 1024 + 1); // 1MB + 1 byte
        $path = tempnam(sys_get_temp_dir(), 'sens-large-');
        file_put_contents($path, $largeContent);

        try {
            (new SmsMessage)
                ->to('01012345678')
                ->file('large-file.jpg', $path);
        } finally {
            unlink($path);
        }
    }

    public function test_it_accepts_file_at_size_limit(): void
    {
        // Create a file exactly at 1MB
        $content = str_repeat('a', 1024 * 1024); // exactly 1MB
        $path = tempnam(sys_get_temp_dir(), 'sens-limit-');
        file_put_contents($path, $content);

        try {
            $message = (new SmsMessage)
                ->to('01012345678')
                ->file('limit-file.jpg', $path);

            $this->assertCount(1, $message->toArray()['files']);
            $this->assertSame('limit-file.jpg', $message->toArray()['files'][0]['name']);
        } finally {
            unlink($path);
        }
    }

    public function test_it_removes_dashes_from_phone_number(): void
    {
        $message = (new SmsMessage)
            ->to('010-1234-5678')
            ->from('055-000-0000');

        $array = $message->toArray();

        $this->assertSame('01012345678', $array['messages'][0]['to']);
        $this->assertSame('0550000000', $array['from']);
    }
}
