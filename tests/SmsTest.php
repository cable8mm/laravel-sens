<?php

namespace Seungmun\Sens\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Seungmun\Sens\Exceptions\SensException;
use Seungmun\Sens\Sms\Sms;

class SmsTest extends TestCase
{
    public function test_it_sends_sms_with_valid_tokens(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'success'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $sms = new Sms([
            'service_id' => 'test-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]);

        // Inject mock client
        $reflection = new \ReflectionClass($sms);
        $property = $reflection->getProperty('http');
        $property->setAccessible(true);
        $property->setValue($sms, $client);

        $params = [
            'type' => 'SMS',
            'contentType' => 'COMM',
            'countryCode' => '82',
            'from' => '0550000000',
            'content' => 'Test message',
            'messages' => [
                ['to' => '01012345678'],
            ],
        ];

        $sms->send($params);

        $this->addToAssertionCount(1);
    }

    public function test_it_throws_exception_with_invalid_tokens(): void
    {
        $this->expectException(SensException::class);
        $this->expectExceptionMessage('NCP tokens are invalid.');

        $sms = new Sms([
            'service_id' => '',
            'access_key' => '',
            'secret_key' => '',
        ]);

        $sms->send([]);
    }

    public function test_it_throws_exception_on_api_error(): void
    {
        $this->expectException(SensException::class);

        $mock = new MockHandler([
            new Response(500, [], json_encode(['error' => 'Internal Server Error'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $sms = new Sms([
            'service_id' => 'test-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
        ]);

        // Inject mock client
        $reflection = new \ReflectionClass($sms);
        $property = $reflection->getProperty('http');
        $property->setAccessible(true);
        $property->setValue($sms, $client);

        $params = [
            'type' => 'SMS',
            'contentType' => 'COMM',
            'countryCode' => '82',
            'from' => '0550000000',
            'content' => 'Test message',
            'messages' => [
                ['to' => '01012345678'],
            ],
        ];

        $sms->send($params);
    }
}
