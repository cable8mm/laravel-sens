<?php

namespace Seungmun\Sens\Tests;

use GuzzleHttp\Client;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class SensSmsChannelTest extends TestCase
{
    /**
     * @var MockInterface|Client
     */
    private $guzzleHttp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzleHttp = m::mock(Client::class);
    }

    /**
     * @return void
     */
    public function test_iam_sorry_because_tests_are_so_nuisance()
    {
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        m::close();
    }
}
