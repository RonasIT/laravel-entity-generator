<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\TranslationsGenerator;
use RonasIT\Support\Tests\Support\Translation\TranslationMockTrait;

class TranslationGeneratorTest extends TestCase
{
    use TranslationMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testCreate()
    {
        Lang::shouldReceive('get')->andReturn([]);

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('validation.php', 'lang/en/validation.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Translations dump on path: vfs://root/lang/en/validation.php',
        );
    }

    public function testCreateStubNotExist()
    {
        config(['entity-generator.stubs.validation' => 'incorrect_stub']);

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('resources/lang/en/validation.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of validation has been skipped cause the view incorrect_stub from the config entity-generator.stubs.validation is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testAppendNotFoundException()
    {
        $this->mockFilesystemForAppend('validation_without_exceptions');

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('validation_append_not_found_exception.php', 'lang/en/validation.php');

        Event::assertNothingDispatched();
    }

    public function testAppendExceptionsCommentStubNotExist()
    {
        config(['entity-generator.stubs.validation_exceptions_comment' => 'incorrect_stub']);

        $this->mockFilesystemForAppend('validation_without_exceptions');

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('validation.php', 'resources/lang/en/validation.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of validation exceptions comment has been skipped cause the view incorrect_stub from the config entity-generator.stubs.validation_exceptions_comment is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testAppendValidationExceptionsExist()
    {
        $this->mockFilesystemForAppend('validation_with_exceptions');

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('validation_append_not_found_with_exceptions.php', 'lang/en/validation.php');

        Event::assertNothingDispatched();
    }
}
