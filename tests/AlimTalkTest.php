<?php

namespace Seungmun\Sens\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Seungmun\Sens\AlimTalk\AlimTalk;
use Seungmun\Sens\Exceptions\SensException;

class AlimTalkTest extends TestCase
{
    public function test_it_sends_alimtalk_with_valid_tokens(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['status' => 'success'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $alimtalk = new AlimTalk([
            'service_id' => 'test-service-id',
            'alimtalk_service_id' => 'test-alimtalk-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
            'plus_friend_id' => '@test',
        ]);

        // Inject mock client
        $reflection = new \ReflectionClass($alimtalk);
        $property = $reflection->getProperty('http');
        $property->setAccessible(true);
        $property->setValue($alimtalk, $client);

        $params = [
            'plusFriendId' => '@test',
            'templateCode' => 'TEMPLATE001',
            'messages' => [
                [
                    'countryCode' => '82',
                    'to' => '01012345678',
                    'content' => 'Test message',
                ],
            ],
        ];

        $alimtalk->send($params);

        $this->addToAssertionCount(1);
    }

    public function test_it_throws_exception_with_invalid_tokens(): void
    {
        $this->expectException(SensException::class);
        $this->expectExceptionMessage('NCP tokens are invalid.');

        $alimtalk = new AlimTalk([
            'service_id' => '',
            'alimtalk_service_id' => '',
            'access_key' => '',
            'secret_key' => '',
        ]);

        $alimtalk->send([]);
    }

    public function test_it_throws_exception_on_api_error(): void
    {
        $this->expectException(SensException::class);

        $mock = new MockHandler([
            new Response(500, [], json_encode(['error' => 'Internal Server Error'])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $alimtalk = new AlimTalk([
            'service_id' => 'test-service-id',
            'alimtalk_service_id' => 'test-alimtalk-service-id',
            'access_key' => 'test-access-key',
            'secret_key' => 'test-secret-key',
            'plus_friend_id' => '@test',
        ]);

        // Inject mock client
        $reflection = new \ReflectionClass($alimtalk);
        $property = $reflection->getProperty('http');
        $property->setAccessible(true);
        $property->setValue($alimtalk, $client);

        $params = [
            'plusFriendId' => '@test',
            'templateCode' => 'TEMPLATE001',
            'messages' => [
                [
                    'countryCode' => '82',
                    'to' => '01012345678',
                    'content' => 'Test message',
                ],
            ],
        ];

        $alimtalk->send($params);
    }
}
