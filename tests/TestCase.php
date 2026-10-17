<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Seed the isolated in-memory test database once per run so feature tests
     * never touch the development database.
     */
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // The array cache outlives each test transaction, so stale SystemSetting
        // values must not leak between tests.
        Cache::flush();

        $this->withoutVite();
    }
}
