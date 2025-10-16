<?php

namespace RonasIT\Support\Tests;

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
            $this->classExistsMethodCall(['resources', 'PostResource']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostResource cause it already exists. Remove /app/Http/Resources/PostResource.php and run command again.',
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
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostsCollectionResource cause it already exists. Remove /app/Http/Resources/PostsCollectionResource.php and run command again.',
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
            ->setFields([
                'integer' => ['priority'],
                'integer-required' => ['media_id'],
                'float' => ['seo_score'],
                'float-required' => ['rating'],
                'string' => ['description'],
                'string-required' => ['title'],
                'boolean' => ['is_reviewed'],
                'boolean-required' => ['is_published'],
                'timestamp' => ['reviewed_at', 'created_at', 'updated_at'],
                'timestamp-required' => ['published_at'],
                'json' => ['meta'],
            ])
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
