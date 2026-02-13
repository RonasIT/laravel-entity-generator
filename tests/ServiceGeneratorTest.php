<?php

namespace RonasIT\EntityGenerator\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\EntityGenerator\DTO\RelationsDTO;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Events\WarningEvent;
use RonasIT\EntityGenerator\Exceptions\ResourceAlreadyExistsException;
use RonasIT\EntityGenerator\Exceptions\ResourceNotExistsException;
use RonasIT\EntityGenerator\Generators\ServiceGenerator;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

class ServiceGeneratorTest extends TestCase
{
    use GeneratorMockTrait;

    public function testMissingRepository()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository'], false),
        ]);

        $this->assertExceptionThrew(
            className: ResourceNotExistsException::class,
            message: 'Cannot create PostService cause PostRepository does not exist. Create app/Repositories/PostRepository.php and run command again.',
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
            $this->classExistsMethodCall(['services', 'PostService'], false),
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
                'string' => ['title'],
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
            $this->classExistsMethodCall(['services', 'PostService'], false),
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

    public function testRepositoryAlreadyExists()
    {
        $this->mockClass(ServiceGenerator::class, [
            $this->classExistsMethodCall(['repositories', 'PostRepository']),
            $this->classExistsMethodCall(['services', 'PostService']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create PostService cause it already exists. Remove app/Services/PostService.php and run command again.',
        );

        app(ServiceGenerator::class)
            ->setModel('Post')
            ->generate();
    }
}
