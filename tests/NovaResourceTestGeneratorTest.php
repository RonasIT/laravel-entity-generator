<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Blade;
use Mockery;
use org\bovigo\vfs\vfsStream;
use phpmock\MockBuilder;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceTestGenerator;

class NovaResourceTestGeneratorTest extends TestCase
{
    public function testCreateForNonExistingNovaResource()
    {
        $builder = new MockBuilder();
        $builder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('class_exists')
            ->setFunction(function () {
                return true;
            });

        $mock = $builder->build();
        $mock->enable();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create PostNovaTest cause Post Nova resource does not exist. Create Post Nova resource.");

        $generatorMock = Mockery::mock(NovaResourceTestGenerator::class)->makePartial();
        $generatorMock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->andReturn(false);

        $generatorMock
            ->setModel('Post')
            ->generate();

        $mock->disable();
    }

    public function testCreateForExistingNovaResourceTest()
    {
        $builder = new MockBuilder();
        $builder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('class_exists')
            ->setFunction(function () {
                return true;
            });

        $mock = $builder->build();
        $mock->enable();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage("Cannot create PostNovaTest cause it's already exist. Remove PostNovaTest.");

        $generatorMock = Mockery::mock(NovaResourceTestGenerator::class)->makePartial();

        $generatorMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'Post')
            ->andReturn(true);

        $generatorMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'PostNovaTest')
            ->andReturn(true);

        $generatorMock
            ->setModel('Post')
            ->generate();

        $mock->disable();
    }

    public function testCreateWithActions()
    {
        $mocks = $this->mockClasses();
        $generatorMock = $this->mockGenerator();
        $this->mockFilesystem();
        $this->mockViewsNamespace();

        $generatorMock
            ->setModel('Post')
            ->setPaths([
                'nova' => 'app/Nova',
                'nova_actions' => 'app/Nova/Actions',
                'tests' => 'tests',
            ])
            ->generate();

        foreach ($mocks as $mock) {
            $mock->disable();
        }
    }

    protected function mockViewsNamespace()
    {
        app('view')->addNamespace('tests', '/app/stubs');
    }

    protected function mockClasses(): array
    {
        $classExistsBuilder = new MockBuilder();
        $classExistsBuilder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('class_exists')
            ->setFunction(function () {
                return true;
            });

        $classExistsMock = $classExistsBuilder->build();
        $classExistsMock->enable();

        $fileExistsBuilder = new MockBuilder();
        $fileExistsBuilder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('file_exists')
            ->setFunction(function () {
                return false;
            });

        $fileExistsMock = $fileExistsBuilder->build();
        $fileExistsMock->enable();

        $configBuilder = new MockBuilder();
        $configBuilder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('config')
            ->setFunction(function () {
                return 'tests::nova_resource_test';
            });

        $configMock = $configBuilder->build();
        $configMock->enable();

        return [
            $classExistsMock,
            $fileExistsMock,
            $configMock
        ];
    }

    protected function mockGenerator()
    {
        $generatorMock = Mockery::mock(NovaResourceTestGenerator::class)->makePartial();
        $generatorMock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'Post')
            ->andReturn(true);

        $generatorMock
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'PostNovaTest')
            ->andReturn(false);

        return $generatorMock;
    }

    protected function mockFilesystem()
    {
        $structure = [
            'app' => [
                'Nova' => [
                    'Actions' => [
                        'PublishPostAction.php',
                        'BlockCommentAction.php',
                        'UnPublishPostAction.txt',
                    ],
                    'Post.php'
                ]
            ],
            'tests'
        ];

        vfsStream::setup('app', $structure);
    }
}
