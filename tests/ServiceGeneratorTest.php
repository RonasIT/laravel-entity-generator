<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ServiceGenerator;
use RonasIT\Support\Tests\Support\Service\ServiceMockTrait;

class ServiceGeneratorTest extends TestCase
{
    use ServiceMockTrait;

    public function testMissingModel()
    {
        $this->mockGeneratorForMissingModel();

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage("Cannot create Post Model cause Post Model does not exists. "
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model");

        app(ServiceGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateWithTrait()
    {
        $this->mockViewsNamespace();
        $this->mockConfigurations();
        $this->mockFilesystemForServiceWithTrait();
        $this->mockGeneratorForServiceWithTrait();

        app(ServiceGenerator::class)
            ->setRelations([
                'hasOne' => [],
                'belongsTo' => ['User'],
                'hasMany' => ['Comment'],
                'belongsToMany' => []
            ])
            ->setFields([
                'integer-required' => ['media_id'],
                'string-required' => ['body'],
                'string' => ['title']
            ])
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('service_with_trait.php', 'app/Services/PostService.php');
    }

    public function testCreateWithoutTrait()
    {
        $this->mockViewsNamespace();
        $this->mockConfigurations();
        $this->mockFilesystemForServiceWithoutTrait();
        $this->mockGeneratorForServiceWithoutTrait();

        app(ServiceGenerator::class)
            ->setRelations([
                'hasOne' => [],
                'belongsTo' => ['User'],
                'hasMany' => ['Comment'],
                'belongsToMany' => []
            ])
            ->setFields([
                'integer-required' => ['media_id'],
                'string-required' => ['body'],
                'string' => ['title']
            ])
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('service_without_trait.php', 'app/Services/PostService.php');
    }
}
