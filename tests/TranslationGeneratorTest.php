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

    public function testCreate()
    {
        $translations = $this->getJsonFixture('validation.json');

        Lang::shouldReceive('get')->andReturn($translations);

        $this->mockFilesystem();

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
        $this->mockFilesystemForAppend();

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertGeneratedFileEquals('validation.php', 'lang/en/validation.php');

        Event::assertNothingDispatched();
    }

    public function testAppendNotFoundExceptionStubNotExist()
    {
        config(['entity-generator.stubs.translation_not_found' => 'incorrect_stub']);

        $this->mockFilesystemForAppend();

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertFileDoesNotExist('validation.php', 'resources/lang/en/validation.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of translation not found has been skipped cause the view incorrect_stub from the config entity-generator.stubs.translation_not_found is not exists. Please check that config has the correct view name value.',
        );
    }
}
