<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\View\ViewException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Tests\Support\Factory\FactoryMockTrait;

class FactoryGeneratorTest extends TestCase
{
    use FactoryMockTrait;

    public function testModelNotExists()
    {
        Event::fake();

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create PostFactory cause Post Model does not exists. "
            . "Create a Post Model by itself or run command 'php artisan make:entity Post --only-model'."
        );

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testFactoryClassExists()
    {
        Event::fake();

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: "Cannot create PostFactory cause PostFactory already exists. Remove PostFactory.",
        );

        $this->getFactoryGeneratorMockForExistingFactory();

        app(FactoryGenerator::class)
            ->setModel('Post')
            ->generate();

        Event::assertNothingDispatched();
    }

    public function testProcessUnknownFieldType()
    {
        $this->mockConfigurations();
        $this->mockFilesystem();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage("Cannot generate fake data for unsupported another_type field type. "
            . "Supported custom field types are json");

        app(FactoryGenerator::class)
            ->setFields([
                'another_type' => ['some_field'],
            ])
            ->setRelations([
                'hasOne' => [],
                'hasMany' => [],
                'belongsTo' => [],
            ])
            ->setModel('Post')
            ->generate();

    }

    public function testCreateSuccess()
    {
        Event::fake();

        $this->mockConfigurations();
        $this->mockFilesystem();

        app(FactoryGenerator::class)
            ->setFields([
                'integer-required' => ['author_id'],
                'string' => ['title', 'iban', 'something'],
                'json' => ['json_text'],
            ])
            ->setRelations([
                'hasOne' => ['user'],
                'hasMany' => [],
                'belongsTo' => ['user'],
            ])
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('post_factory.php', '/database/factories/PostFactory.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Factory: PostFactory',
        );
    }
}
