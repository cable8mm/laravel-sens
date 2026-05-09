<?php

namespace Seungmun\Sens\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Seungmun\Sens\SensServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            SensServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('laravel-sens.service_id', 'sms-service-id');
        $app['config']->set('laravel-sens.alimtalk_service_id', 'alimtalk-service-id');
        $app['config']->set('laravel-sens.plus_friend_id', '@sens');
        $app['config']->set('laravel-sens.access_key', 'access-key');
        $app['config']->set('laravel-sens.secret_key', 'secret-key');
        $app['config']->set('services.sens.services.sms.sender', '055-000-0000');
    }
}
