<?php

namespace RonasIT\Support\Tests\Support\NovaTestGeneratorTest;

use Mockery;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait NovaTestGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockNovaRequestClassCall(): void
    {
        $mock = Mockery::mock('alias:Laravel\Nova\Http\Requests\NovaRequest');

        $this->app->instance('Laravel\Nova\Http\Requests\NovaRequest', $mock);
    }

    public function mockFilesystem(): void
    {
        $this->fileSystemMock = new FileSystemMock;

        $this->fileSystemMock->novaActions = [
            'PublishPostAction.php' => $this->mockPhpFileContent(),
            'ArchivePostAction.php' => $this->mockPhpFileContent(),
            'BlockCommentAction.php' => $this->mockPhpFileContent(),
            'UnPublishPostAction.txt' => 'text',
        ];

        $this->fileSystemMock->novaModels = [
            'WelcomeBonusResource.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->models = [
            'WelcomeBonus.php' => $this->mockPhpFileContent(),
        ];

        $this->fileSystemMock->testFixtures = [
            'NovaWelcomeBonusTest' => []
        ];

        $this->fileSystemMock->setStructure();
    }
}
