<?php

namespace RonasIT\Support\Tests\Support\NovaTestGeneratorTest;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockClassTrait;

trait NovaTestGeneratorMockTrait
{
    use GeneratorMockTrait, MockClassTrait;

    public function mockNovaResourceTestGenerator(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            [
                'method' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'method' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'method' => 'getModelFields',
                'arguments' => ['Post'],
                'result' => ['title', 'name']
            ],
            [
                'method' => 'getMockModel',
                'arguments' => ['Post'],
                'result' => ['title' => 'some title', 'name' => 'some name']
            ],
            [
                'method' => 'getMockModel',
                'arguments' => ['Post'],
                'result' => ['title' => 'some title', 'name' => 'some name']
            ],
            [
                'method' => 'loadNovaActions',
                'arguments' => [],
                'result' => [
                    new PublishPostAction,
                    new UnPublishPostAction,
                    new UnPublishPostAction,
                ]
            ],
            [
                'method' => 'loadNovaFields',
                'arguments' => [],
                'result' => [
                    new TextField,
                    new DateField,
                ]
            ],
            [
                'method' => 'loadNovaFilters',
                'arguments' => [],
                'result' => [
                    new CreatedAtFilter,
                ]
            ],
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Nova' => [
                    'Actions' => [
                        'PublishPostAction.php' => '<?php',
                        'ArchivePostAction.php' => '<?php',
                        'BlockCommentAction.php' => '<?php',
                        'UnPublishPostAction.txt' => 'text',
                    ],
                    'Post.php' => '<?php'
                ],
                'Models' => [
                    'Post.php' => '<?php'
                ]
            ],
            'tests' => [
                'fixtures' => [
                    'NovaPostTest' => []
                ]
            ]
        ];

        vfsStream::create($structure);
    }
}