<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use RonasIT\Support\Traits\FixturesTrait;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use FixturesTrait, InteractsWithViews;

    protected $globalExportMode = false;

    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=true');
    }
}
