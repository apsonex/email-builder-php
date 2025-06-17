<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        $this->loadEnvironmentVariables();

        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            //
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        //
    }

    protected function loadEnvironmentVariables()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..' , '.env.testing');

        $dotenv->load();
    }
}
