<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Generators\ResourceGenerator;
use RonasIT\Support\Tests\Support\Resource\ResourceMockTrait;

class ResourceGeneratorTest extends TestCase
{
    use ResourceMockTrait;

    public function testResourceAlreadyExists()
    {
        $this->mockGeneratorForAlreadyExistsResource();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage('Cannot create PostResource cause PostResource already exists. Remove PostResource.');

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCollectionResourceAlreadyExists()
    {
        $this->mockGeneratorForAlreadyExistsCollectionResource();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectErrorMessage('Cannot create PostsCollectionResource cause PostsCollectionResource already exists. '
            . 'Remove PostsCollectionResource.');

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateResources()
    {
        $this->mockConfigurations();
        $this->mockViewsNamespace();
        $this->mockFilesystem();

        app(ResourceGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('post_resource.php', 'app/Http/Resources/PostResource.php');
        $this->assertGeneratedFileEquals('post_collection_resource.php', 'app/Http/Resources/PostsCollectionResource.php');
    }
}
