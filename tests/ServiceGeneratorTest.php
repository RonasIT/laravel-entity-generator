<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ServiceGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

class ServiceGeneratorTest extends TestCase
{
    use GeneratorMockTrait;

    public function testMissingRepository()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository'], false),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create PostService cause PostRepository does not exists. '
            . "Create a PostRepository by himself or run command 'php artisan make:entity Post --only-repository'",
        );

        app(ServiceGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testCreate()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository']),
        ]);

        app(ServiceGenerator::class)
            ->setRelations(
                new RelationsDTO(
                    hasMany: ['Comment'],
                    belongsTo: ['User'],
                ))
            ->setFields([
                'integer-required' => ['media_id'],
                'string-required' => ['body'],
                'string' => ['title']
            ])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('service.php', 'app/Services/PostService.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Service: PostService',
        );
    }

    public function testCreateStubNotExist()
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
