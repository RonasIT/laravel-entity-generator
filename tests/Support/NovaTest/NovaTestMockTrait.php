<?php

namespace RonasIT\Support\Tests\Support\NovaTest;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait NovaTestMockTrait
{
    use GeneratorMockTrait;

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

    public function mockTestGeneratorForNonExistingNovaResource(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => [],
                'result' => false
            ]
        ]);
    }

    public function mockGeneratorForExistingNovaTest(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['nova', 'Post'],
                'result' => true
            ],
            [
                'method' => 'classExists',
                'arguments' => ['nova', 'NovaPostTest'],
                'result' => true
            ]
        ]);
    }

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.nova_test' => 'entity-generator::nova_test',
            'entity-generator.stubs.dump' => 'entity-generator::dumps.pgsql',
            'entity-generator.paths' => [
                'nova' => 'app/Nova',
                'nova_actions' => 'app/Nova/Actions',
                'tests' => 'tests',
                'models' => 'app/Models'
            ]
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