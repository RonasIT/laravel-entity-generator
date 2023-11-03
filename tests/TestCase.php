<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Traits\FixturesTrait;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use FixturesTrait;

    protected $globalExportMode = false;

    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=true');
    }
}
