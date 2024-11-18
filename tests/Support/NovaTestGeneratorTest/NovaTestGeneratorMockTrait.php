<?php

namespace RonasIT\Support\Tests\Support\NovaTestGeneratorTest;

use Mockery;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait NovaTestGeneratorMockTrait
{
    use GeneratorMockTrait;
    use MockTrait;

    public function mockNovaResourceTestGenerator(): void
    {
        $mock = Mockery::mock('alias:Laravel\Nova\Http\Requests\NovaRequest');

        $this->app->instance('Laravel\Nova\Http\Requests\NovaRequest', $mock);
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock;

        $fileSystemMock->novaActions = [
            'PublishPostAction.php' => $this->mockPhpFileContent(),
            'ArchivePostAction.php' => $this->mockPhpFileContent(),
            'BlockCommentAction.php' => $this->mockPhpFileContent(),
            'UnPublishPostAction.txt' => 'text',
        ];

        $fileSystemMock->novaModels = [
            'WelcomeBonusResource.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->models = [
            'WelcomeBonus.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->testFixtures = [
            'NovaWelcomeBonusTest' => []
        ];

        $fileSystemMock->setStructure();
    }
}
