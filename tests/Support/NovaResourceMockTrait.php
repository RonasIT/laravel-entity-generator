<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use phpmock\Mock;
use phpmock\MockBuilder;
use RonasIT\Support\Generators\NovaTestGenerator;

trait NovaResourceMockTrait
{
    public function mockNovaResourceTestGenerator(): void
    {
        $mock = $this
            ->getMockBuilder(NovaTestGenerator::class)
            ->onlyMethods(['getModelFields', 'getMockModel'])
            ->getMock();

        $mock
            ->method('getModelFields')
            ->willReturn(['title', 'name']);

        $mock
            ->method('getMockModel')
            ->willReturn(['title' => 'some title', 'name' => 'some name']);

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
            'entity-generator.stubs.nova_resource_test' => 'entity-generator::nova_test',
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
        $classExistsBuilder = new MockBuilder();
        $classExistsBuilder
            ->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('class_exists')
            ->setFunction(function () {
                return true;
            });

        $classExistsMock = $classExistsBuilder->build();
        $classExistsMock->enable();

        return $classExistsMock;
    }

    public function mockFileExists(): Mock
    {
        $fileExistsBuilder = new MockBuilder();
        $fileExistsBuilder
            ->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('file_exists')
            ->setFunction(function () {
                return false;
            });

        $fileExistsMock = $fileExistsBuilder->build();
        $fileExistsMock->enable();

        return $fileExistsMock;
    }

    public function mockFilesystem()
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