<?php

namespace Seungmun\Sens\Tests;

use Seungmun\Sens\Sms\Sms;

class SensAssertValidTokensTest extends TestCase
{
    public function test_it_returns_true_when_all_tokens_are_valid(): void
    {
        $sens = new Sms([
            'service_id' => 'test-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]);

        $this->assertTrue($sens->assertValidTokens());
    }

    public function test_it_returns_false_when_service_id_is_empty(): void
    {
        $sens = new Sms([
            'service_id' => '',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]);

        $this->assertFalse($sens->assertValidTokens());
    }

    public function test_it_returns_false_when_access_key_is_empty(): void
    {
        $sens = new Sms([
            'service_id' => 'test-service-id',
            'access_key' => '',
            'secret_key' => 'test-secret-key',
        ]);

        $this->assertFalse($sens->assertValidTokens());
    }

    public function test_it_returns_false_when_secret_key_is_empty(): void
    {
        $sens = new Sms([
            'service_id' => 'test-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => '',
        ]);

        $this->assertFalse($sens->assertValidTokens());
    }

    public function test_it_returns_false_when_all_tokens_are_empty(): void
    {
        $sens = new Sms([
            'service_id' => '',
            'access_key' => '',
            'secret_key' => '',
        ]);

        $this->assertFalse($sens->assertValidTokens());
    }
}
