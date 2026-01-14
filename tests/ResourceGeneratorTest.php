<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\FieldsSchemaDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Generators\ResourceGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

class ResourceGeneratorTest extends TestCase
{
    use GeneratorMockTrait;

    public function testResourceAlreadyExists()
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostResource cause it already exists. Remove app/Http/Resources/Post/PostResource.php and run command again.',
        );

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCollectionResourceAlreadyExists()
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'Post/PostsCollectionResource']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostsCollectionResource cause it already exists. Remove app/Http/Resources/Post/PostsCollectionResource.php and run command again.',
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

    public function testCreateResourcesWithFields()
    {
        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setFields(FieldsSchemaDTO::fromArray($this->getJsonFixture('create_resource_fields')))
            ->generate();

        $this->assertGeneratedFileEquals('post_resource_with_fields.php', 'app/Http/Resources/Post/PostResource.php');
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
