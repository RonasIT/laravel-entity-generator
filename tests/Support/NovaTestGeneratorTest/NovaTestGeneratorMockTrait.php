<?php

namespace RonasIT\Support\Tests\Support\NovaTestGeneratorTest;

use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait NovaTestGeneratorMockTrait
{
    use GeneratorMockTrait;
    use MockTrait;

    public function mockNovaResourceTestGenerator(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            [
                'function' => 'getMockModel',
                'arguments' => ['WelcomeBonus'],
                'result' => ['title' => 'some title', 'name' => 'some name']
            ],
            [
                'function' => 'getMockModel',
                'arguments' => ['WelcomeBonus'],
                'result' => ['title' => 'some title', 'name' => 'some name']
            ],
            [
                'function' => 'loadNovaActions',
                'arguments' => [],
                'result' => [
                    new PublishPostAction,
                    new UnPublishPostAction,
                    new UnPublishPostAction,
                ]
            ],
            [
                'function' => 'loadNovaFields',
                'arguments' => [],
                'result' => [
                    new TextField,
                    new DateField,
                ]
            ],
            [
                'function' => 'loadNovaFilters',
                'arguments' => [],
                'result' => [
                    new CreatedAtFilter,
                ]
            ],
        ]);
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
            'WelcomeBonus.php' => $this->mockPhpFileContent(),
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
