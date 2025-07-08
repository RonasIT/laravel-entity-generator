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

        $fileSystemMock->novaResources = [
            'WelcomeBonusDraftResource.php' => $this->mockPhpFileContent(),
            'PostResource.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->models = [
            'WelcomeBonus.php' => $this->mockPhpFileContent(),
            'Post.php' => $this->mockPhpFileContent(),
            'News.php' => $this->mockPhpFileContent(),
        ];

        $fileSystemMock->testFixtures = [
            'NovaWelcomeBonusTest' => []
        ];

        $fileSystemMock->setStructure();
    }
}
