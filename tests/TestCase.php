<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected function setUp(): void {
        parent::setUp();

        $this->seed([
            DatabaseSeeder::class
        ]);

        $this->app['config']->set('app.url', ['administration.test']);

        $this->app['config']->set('lexoffice.access_token', env('LEXOFFICE_ACCESS_TOKEN'));
    }
}
