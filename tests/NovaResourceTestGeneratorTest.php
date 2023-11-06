<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\View;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use phpmock\MockBuilder;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceTestGenerator;

class NovaResourceTestGeneratorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->mockFilesystem();
        $this->app->setBasePath(vfsStream::url('root'));
        View::addNamespace('entity-generator', '/app/stubs');
    }

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
        config()->set('entity-generator.stubs.nova_resource_test', 'entity-generator::nova_resource_test');
        config()->set('entity-generator.paths', [
            'nova' => 'app/Nova',
            'nova_actions' => 'app/Nova/Actions',
            'tests' => 'tests',
        ]);
        $er = file_exists(vfsStream::url('root/app'));
        $yt = 234;

        $mocks = $this->mockClasses();
        $generatorMock = $this->mockGenerator();
        $this->mockViewsNamespace();

        $generatorMock = new NovaResourceTestGenerator();
        $generatorMock
            ->setModel('Post')
            ->generate();

        foreach ($mocks as $mock) {
            $mock->disable();
        }
        $ytr = vfsStream::url('app');
        $dfgd = vfsStream::inspect(new vfsStreamStructureVisitor())
            ->getStructure();
        $this->assertEquals(
            [],
            vfsStream::inspect(new vfsStreamStructureVisitor())
                ->getStructure()
        );
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

        return [
            $classExistsMock,
            $fileExistsMock,
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
                    'Post.php' => '<?php'
                ]
            ],
            'tests' => []
        ];

        vfsStream::setup('root', null, $structure);
    }
}
