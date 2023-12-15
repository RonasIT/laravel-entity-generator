<?php

namespace RonasIT\Support\Tests\Support\NovaTest;

use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait NovaTestMockTrait
{
    use GeneratorMockTrait;

    public function mockNovaResourceTestGenerator(): void
    {
        $mock = $this
            ->getMockBuilder(NovaTestGenerator::class)
            ->onlyMethods(['getModelFields', 'getMockModel', 'loadNovaActions', 'loadNovaFields', 'loadNovaFilters'])
            ->getMock();

        $mock
            ->method('getModelFields')
            ->willReturn(['title', 'name']);

        $mock
            ->method('getMockModel')
            ->willReturn(['title' => 'some title', 'name' => 'some name']);

        $mock
            ->method('loadNovaActions')
            ->willReturn([
                new PublishPostAction,
                new UnPublishPostAction,
                new UnPublishPostAction,
            ]);

        $mock
            ->method('loadNovaFields')
            ->willReturn([
                new TextField,
                new DateField,
            ]);

        $mock
            ->method('loadNovaFilters')
            ->willReturn([
                new CreatedAtFilter,
            ]);

        $this->app->instance(NovaTestGenerator::class, $mock);
    }

    public function getTestGeneratorMockForNonExistingNovaResource(): MockInterface
    {
        return $this->getGeneratorMockForNonExistingNovaResource(NovaTestGenerator::class);
    }

    public function getGeneratorMockForExistingNovaTest(): MockInterface
    {
        $mock = Mockery::mock(NovaTestGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'Post')
            ->andReturn(true);

        $mock
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'NovaPostTest')
            ->andReturn(true);

        return $mock;
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