<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;
use org\bovigo\vfs\vfsStream;
use phpmock\Mock;
use RonasIT\Support\Traits\FixturesTrait;

class TestCase extends BaseTestCase
{
    use FixturesTrait, InteractsWithViews;

    protected $globalExportMode = false;
    protected $generatedFileBasePath;

    public function setUp(): void
    {
        parent::setUp();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        $this->app->setBasePath($this->generatedFileBasePath);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Mock::disableAll();
    }

    public function rollbackToDefaultBasePath(): void
    {
        $this->app->setBasePath(getcwd());
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->useEnvironmentPath(__DIR__.'/..');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);
    }

    protected function assertGeneratedFileEquals(string $fixtureName, string $filePath, bool $exportMode = false): void
    {
        $filePath = "{$this->generatedFileBasePath}/$filePath";

        $this->assertFileExists($filePath);

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
