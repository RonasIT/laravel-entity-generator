<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ServiceGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

class ServiceGeneratorTest extends TestCase
{
    use GeneratorMockTrait;

    public function testMissingModel()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository'], false),
            $this->classExistsMethodCall(['models', 'Post'], false),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create PostService cause Post Model does not exists. '
            . "Create a Post Model by himself or run command 'php artisan make:entity Post --only-model'",
        );

        app(ServiceGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testCreateWithTrait()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository'], false),
            $this->classExistsMethodCall(['models', 'Post']),
        ]);

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

        $this->assertGeneratedFileEquals('service_with_trait.php', 'app/Services/PostService.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Service: PostService',
        );
    }

    public function testCreateWithTraitStubNotExist()
    {
        config(['entity-generator.stubs.service_with_trait' => 'incorrect_stub']);

        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository'], false),
            $this->classExistsMethodCall(['models', 'Post']),
        ]);

        app(ServiceGenerator::class)
            ->setFields([])
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('app/Services/PostService.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of service with trait has been skipped cause the view incorrect_stub from the config entity-generator.stubs.service_with_trait is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testCreateWithoutTrait()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository']),
        ]);

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

        $this->assertGeneratedFileEquals('service_without_trait.php', 'app/Services/PostService.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Service: PostService',
        );
    }

    public function testCreateWithoutTraitStubNotExist()
    {
        config(['entity-generator.stubs.service' => 'incorrect_stub']);

        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository']),
        ]);

        app(ServiceGenerator::class)
            ->setFields([])
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('app/Services/PostService.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of service has been skipped cause the view incorrect_stub from the config entity-generator.stubs.service is not exists. Please check that config has the correct view name value.',
        );
    }
}
