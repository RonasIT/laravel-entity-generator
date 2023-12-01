<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RonasIT\Support\Traits\FixturesTrait;

class TestCase extends BaseTestCase
{
    use FixturesTrait, InteractsWithViews;

    protected $globalExportMode = false;
    protected $generatedFileBasePath;

    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=true');
    }

    public function rollbackToDefaultBasePath(): void
    {
        $this->app->setBasePath('/app');
    }

    protected function assertGeneratedFileEquals(string $fixtureName, string $filePath, bool $exportMode = false): void
    {
        $filePath = "{$this->generatedFileBasePath}/$filePath";

        if ($exportMode || $this->globalExportMode) {
            $content = file_get_contents($filePath);

            if (Str::endsWith($fixtureName, '.json')) {
                $content = json_decode($content, true);
                $this->exportJson($fixtureName, $content);
            } else {
                $this->exportContent($content, $fixtureName);
            }
        }

        $this->assertFileEquals($this->getFixturePath($fixtureName), $filePath);
    }

    protected function assertGenerateFileExists(string $path): void
    {
        $this->assertFileExists("{$this->generatedFileBasePath}/{$path}");
    }
}
