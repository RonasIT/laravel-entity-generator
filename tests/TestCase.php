<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as BaseTestCase;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\EntityGeneratorServiceProvider;
use RonasIT\Support\Traits\FixturesTrait;

class TestCase extends BaseTestCase
{
    use FixturesTrait;
    use InteractsWithViews;

    protected bool $globalExportMode = false;
    protected string $generatedFileBasePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockConfigurations();

        vfsStream::setup();

        $this->generatedFileBasePath = vfsStream::url('root');

        Event::fake();

        $this->app->setBasePath($this->generatedFileBasePath);

        Carbon::setTestNow('2022-02-02');
    }

    public function getFixturePath(string $fixtureName): string
    {
        $class = get_class($this);
        $explodedClass = explode('\\', $class);
        $className = Arr::last($explodedClass);

        return getcwd() . "/tests/fixtures/{$className}/{$fixtureName}";
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator' => include('config/entity-generator.php'),
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            EntityGeneratorServiceProvider::class
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->useEnvironmentPath(__DIR__ . '/..');
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

    protected function assertEventPushed(string $className, string $message): void
    {
        Event::assertDispatched(
            event: $className,
            callback: fn ($event) => $event->message === $message,
        );
    }

    protected function assertEventPushedChain(array $expectedEvents): void
    {
        $dispatchedEvents = Event::dispatchedEvents();

        $this->assertEquals(array_keys($expectedEvents), array_keys($dispatchedEvents));

        foreach ($dispatchedEvents as $event => $messages) {
            $messages = array_map(fn ($message) => Arr::first($message)->message, $messages);

            $this->assertEquals($expectedEvents[$event], $messages);
        }
    }

    protected function assertExceptionThrew(string $className, string $message): void
    {
        $this->expectException($className);
        $this->expectExceptionMessage($message);
    }
}
