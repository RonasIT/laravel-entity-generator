<?php

namespace RonasIT\Support\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\View;
use Mockery;
use org\bovigo\vfs\vfsStream;
use phpmock\Mock;
use phpmock\MockBuilder;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaResourceTestGenerator;

class NovaResourceTestGeneratorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        putenv('FAIL_EXPORT_JSON=false');

        vfsStream::setup();

        $this->app->setBasePath(vfsStream::url('root'));
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
        $this->mockFilesystem();

        $this->setupConfigurations();
        $this->mockViewsNamespace();

        $functionMock = $this->mockClassExistsFunction();

        $generator = new NovaResourceTestGenerator();
        $generator
            ->setModel('Post')
            ->generate();

        $this->assertTrue(file_exists(base_path('tests/PostResourceTest.php')));

        $content = file_get_contents(base_path('tests/PostResourceTest.php'));
        $this->app->setBasePath(__DIR__ . '/../');
        $this->exportContent($content, '/created_resource_test.php');

        $fixture = $this->getFixture('created_resource_test.php');

        $this->assertEquals($fixture, $content);

        $functionMock->disable();
    }

    protected function setupConfigurations()
    {
        config()->set('entity-generator.stubs.nova_resource_test', 'entity-generator::nova_resource_test');
        config()->set('entity-generator.paths', [
            'nova' => 'app/Nova',
            'nova_actions' => 'app/Nova/Actions',
            'tests' => 'tests',
        ]);
    }

    protected function mockViewsNamespace()
    {
        View::addNamespace('entity-generator', '/app/stubs');
    }

    protected function mockClassExistsFunction(): Mock
    {
        $classExistsBuilder = new MockBuilder();
        $classExistsBuilder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('class_exists')
            ->setFunction(function () {
                return true;
            });

        $classExistsMock = $classExistsBuilder->build();
        $classExistsMock->enable();

        return $classExistsMock;
    }

    protected function mockFileExists(): Mock
    {
        $fileExistsBuilder = new MockBuilder();
        $fileExistsBuilder->setNamespace('\\RonasIT\\Support\\Generators')
            ->setName('file_exists')
            ->setFunction(function () {
                return false;
            });

        $fileExistsMock = $fileExistsBuilder->build();
        $fileExistsMock->enable();

        return $fileExistsMock;
    }

    protected function mockFilesystem()
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
                ]
            ],
            'tests' => []
        ];

        vfsStream::create($structure);
    }
}
