<?php

namespace RonasIT\EntityGenerator\Tests;

use RonasIT\EntityGenerator\DTO\RelationsDTO;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Events\WarningEvent;
use RonasIT\EntityGenerator\Exceptions\ResourceAlreadyExistsException;
use RonasIT\EntityGenerator\Exceptions\ResourceNotExistsException;
use RonasIT\EntityGenerator\Generators\ResourceGenerator;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

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
            ->setCrudOptions(['C', 'R', 'U', 'D'])
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
            ->setCrudOptions(['R'])
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
            ->setCrudOptions(['C', 'R', 'U', 'D'])
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
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_resource_fields')))
            ->setCrudOptions(['C', 'R', 'U', 'D'])
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

    public function testCreateResourcesWithRelations(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'Comment/CommentResource']),
            $this->classExistsMethodCall(['resources', 'Role/RolesCollectionResource']),
            $this->classExistsMethodCall(['resources', 'User/UserResource']),
            $this->classExistsMethodCall(['resources', 'Tag/TagsCollectionResource']),
            $this->classExistsMethodCall(['resources', 'Post/PostsCollectionResource'], false),
        ]);

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                hasOne: ['Comment'],
                hasMany: ['Role'],
                belongsTo: ['User'],
                belongsToMany: ['Tag'],
            ))
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('post_resource_with_relations.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileEquals('post_collection_resource.php', 'app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Resource: PostResource',
                'Created a new CollectionResource: PostsCollectionResource',
            ],
        ]);
    }

    public function testCreateResourcesWithRegularFieldsAndRelations(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'User/UserResource']),
            $this->classExistsMethodCall(['resources', 'Post/PostsCollectionResource'], false),
        ]);

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO($this->getJsonFixture('create_resource_fields')))
            ->setRelations(new RelationsDTO(
                belongsTo: ['User'],
            ))
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('post_resource_with_regular_fields_and_relations.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileEquals('post_collection_resource.php', 'app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Resource: PostResource',
                'Created a new CollectionResource: PostsCollectionResource',
            ],
        ]);
    }

    public function testCreateResourceWithExplicitlyDeclaredForeignKey(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'User/UserResource']),
        ]);

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setFields($this->getFieldsDTO([
                'integer' => [
                    'user_id',
                ],
                'string' => [
                    'title',
                ],
            ]))
            ->setRelations(new RelationsDTO(
                belongsTo: ['User'],
            ))
            ->setCrudOptions(['C'])
            ->generate();

        $this->assertGeneratedFileEquals('post_resource_with_explicit_foreign_key.php', 'app/Http/Resources/Post/PostResource.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Resource: PostResource',
        );
    }

    public function testRelationResourceNotExists(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'User/UserResource'], false),
        ]);

        $this->assertExceptionThrew(
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostResource cause UserResource does not exist. Create app/Http/Resources/User/UserResource.php and run command again.',
        );

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                belongsTo: ['User'],
            ))
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testRelationCollectionResourceNotExists(): void
    {
        $this->mockClass(ResourceGenerator::class, [
            $this->classExistsMethodCall(['resources', 'Post/PostResource'], false),
            $this->classExistsMethodCall(['resources', 'Role/RolesCollectionResource'], false),
        ]);

        $this->assertExceptionThrew(
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostResource cause RolesCollectionResource does not exist. Create app/Http/Resources/Role/RolesCollectionResource.php and run command again.',
        );

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                hasMany: ['Role'],
            ))
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();
    }

    public function testCreateResourceWithoutCollection()
    {
        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Resource: PostResource',
        );
    }

    public function testSkipResourceCreation()
    {
        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['U', 'D'])
            ->generate();

        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');
    }

    public function testCreateResourcesResourceStubNotExist()
    {
        config(['entity-generator.stubs.resource' => 'incorrect_stub']);

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');

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
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => ['Created a new Resource: PostResource'],
            WarningEvent::class => ['Generation of collection resource has been skipped cause the view incorrect_stub from the config entity-generator.stubs.collection_resource is not exists. Please check that config has the correct view name value.'],
        ]);
    }
}
