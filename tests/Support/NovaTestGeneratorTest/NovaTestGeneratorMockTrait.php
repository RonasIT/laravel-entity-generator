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
                'function' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'function' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'function' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'function' => 'getMockModel',
                'arguments' => ['Post'],
                'result' => ['title' => 'some title', 'name' => 'some name']
            ],
            [
                'function' => 'getMockModel',
                'arguments' => ['Post'],
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
            'PublishPostAction.php' => '<?php',
            'ArchivePostAction.php' => '<?php',
            'BlockCommentAction.php' => '<?php',
            'UnPublishPostAction.txt' => 'text',
        ];

        $fileSystemMock->novaModels = [
            'Post.php' => '<?php'
        ];

        $fileSystemMock->models = [
            'Post.php' => '<?php'
        ];

        $fileSystemMock->testFixtures = [
            'NovaPostTest' => []
        ];

        $fileSystemMock->setStructure();
    }
}
