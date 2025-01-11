<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Generators\ResourceGenerator;
use RonasIT\Support\Tests\Support\Resource\ResourceMockTrait;

class ResourceGeneratorTest extends TestCase
{
    use ResourceMockTrait;

    public function testResourceAlreadyExists()
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'PostResource']),
        ]);

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: 'Cannot create PostResource cause PostResource already exists. Remove PostResource.',
        );

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCollectionResourceAlreadyExists()
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'PostResource'], false),
            $this->classExistsMethodCall(['resources', 'PostsCollectionResource']),
        ]);

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: 'Cannot create PostsCollectionResource cause PostsCollectionResource already exists. '
            . 'Remove PostsCollectionResource.',
        );

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/Post/PostResource.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Resource: PostResource',
        );
    }

    public function testCreateResources()
    {
        $this->mockFilesystem();

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileEquals('post_collection_resource.php', 'app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Resource: PostResource',
                'Created a new CollectionResource: PostsCollectionResource',
            ],
        ]);
    }

    public function testCreateResourcesResourceStubNotExist()
    {
        config(['entity-generator.stubs.resource' => 'incorrect_stub']);

        $this->mockFilesystem();

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('app/Http/Resources/Post/PostResource.php');
        $this->assertFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of resource has been skipped cause the view incorrect_stub from the config entity-generator.stubs.resource is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testCreateResourcesCollectionResourceStubNotExist()
    {
        config(['entity-generator.stubs.collection_resource' => 'incorrect_stub']);

        $this->mockFilesystem();

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => ['Created a new Resource: PostResource'],
            WarningEvent::class => ['Generation of collection resource has been skipped cause the view incorrect_stub from the config entity-generator.stubs.collection_resource is not exists. Please check that config has the correct view name value.'],
        ]);
    }
}
