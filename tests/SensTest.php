<?php

namespace Seungmun\Sens\Tests;

use Seungmun\Sens\Sens;

class SensTest extends TestCase
{
    public function test_it_generates_the_ncloud_signature_without_deprecated_encoding_helpers(): void
    {
        $sens = new class(['service_id' => 'sms-service-id', 'access_key' => 'access-key', 'secret_key' => 'secret-key']) extends Sens
        {
            public function send(array $params): void {}
        };

        $message = "POST /sms/v2/services/sms-service-id/messages\n1700000000000\naccess-key";
        $expected = base64_encode(hex2bin(hash_hmac('sha256', $message, 'secret-key')));

        $this->assertSame(
            $expected,
            $sens->makeSignature('POST', '/sms/v2/services/sms-service-id/messages', '1700000000000')
        );
    }
}
