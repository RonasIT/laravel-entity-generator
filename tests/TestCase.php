<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\FixturesTrait;

class TestCase extends BaseTestCase
{
    use FixturesTrait, InteractsWithViews;

    protected $globalExportMode = false;

    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=true');
    }

    public function rollbackToDefaultBasePath(): void
    {
        $this->app->setBasePath(__DIR__ . '/../');
    }

    public function loadJSONContent(string $path): array
    {
        return json_decode(file_get_contents(base_path($path)), true);
    }

    public function loadFileContent(string $path): string
    {
        return file_get_contents(base_path($path));
    }

    public function assertEqualsFixture(string $fixture, $data, bool $exportMode = false): void
    {
        if (substr($fixture, -5) === '.json') {
            $this->assertEqualsJSONFixture($fixture, $data, $exportMode);
        } else {
            $this->assertEqualsGeneralFixture($fixture, $data, $exportMode);
        }
    }

    public function assertEqualsJSONFixture(string $fixture, $data, bool $exportMode = false): void
    {
        if ($exportMode || $this->globalExportMode) {
            $this->exportJson($fixture, $data);
        }

        $this->assertEquals($this->getJsonFixture($fixture), $data);
    }

    public function assertEqualsGeneralFixture(string $fixture, $data, bool $exportMode = false): void
    {
        if ($exportMode || $this->globalExportMode) {
            $this->exportContent($data, $fixture);
        }

        $this->assertEquals($this->getFixture($fixture), $data);
    }

    public function generatedFileExists(string $path): bool
    {
        return file_exists(base_path($path));
    }
}
