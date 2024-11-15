<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestGeneratorMockTrait;

    public function testCreateNovaTestsResourceNotExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource'], false),
            $this->classExistsMethodCall(['nova', 'PostResource'], false),
            $this->classExistsMethodCall(['nova', 'Post'], false),
        ]);

        $this->assertExceptionThrowed(
            className: ClassNotExistsException::class,
            message: 'Cannot create NovaPostTest cause Post Nova resource does not exist. Create Post Nova resource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource']),
            $this->classExistsMethodCall(['nova', 'NovaPostTest'])
        ]);

        $this->assertExceptionThrowed(
            className: ClassAlreadyExistsException::class,
            message: "Cannot create NovaPostTest cause it's already exist. Remove NovaPostTest.",
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testSuccess()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockFilesystem();
        $this->mockNovaResourceTestGenerator();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/update_welcome_bonus_request.json');
    }

    public function testCreateWithMissingNovaPackage()
    {
        Event::fake();

        $this->mockNovaServiceProviderExists(false);

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Nova is not installed and NovaTest is skipped',
        );
    }
}
