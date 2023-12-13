<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use phpmock\Mock;
use phpmock\MockBuilder;
use RonasIT\Support\Generators\NovaTestGenerator;

trait NovaTestMockTrait
{
    public function mockNativeFunction(string $namespace, string $name, $result): Mock
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName($name)
            ->setFunction(function () use ($result) {
                return $result;
            });

        $mock = $builder->build();
        $mock->enable();

        return $mock;
    }

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

    public function getGeneratorMockForNonExistingNovaResource(): MockInterface
    {
        $mock = Mockery::mock(NovaTestGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->andReturn(false);

        return $mock;
    }

    public function getGeneratorMockForExistingNovaResourceTest(): MockInterface
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

    public function mockViewsNamespace(): void
    {
        View::addNamespace('entity-generator', '/app/stubs');
    }

    public function mockClassExistsFunction(): Mock
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'class_exists', true);
    }

    public function mockFileExists(): Mock
    {
        return $this->mockNativeFunction( '\\RonasIT\\Support\\Generators', 'file_exists', false);
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