<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Tests\Support\Models\WelcomeBonus;
use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;
use Laravel\Nova\NovaServiceProvider;
use Mockery;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestGeneratorMockTrait;

    public function testGenerateResourceNotExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource'], false),
            $this->classExistsMethodCall(['nova', 'PostResource'], false),
            $this->classExistsMethodCall(['nova', 'Post'], false),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create NovaPostTest cause Post Nova resource does not exist. Create Post Nova resource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testGenerateNovaTestAlreadyExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'PostNovaResource']),
            $this->classExistsMethodCall(['nova', 'NovaPostTest'])
        ]);

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: "Cannot create NovaPostTest cause it's already exist. Remove NovaPostTest.",
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testNovaTestStubNotExist()
    {
        Event::fake();

        $this->mockNovaServiceProviderExists();

        $this->mockFilesystem();
        $this->mockNovaRequestClassCall();

        config(['entity-generator.stubs.nova_test' => 'incorrect_stub']);

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertFileDoesNotExist('tests/NovaWelcomeBonusTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/update_welcome_bonus_request.json');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of nova test has been skipped cause the view incorrect_stub from the config entity-generator.stubs.nova_test is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testDumpStubNotExist()
    {
        Event::fake();

        $this->mockNovaServiceProviderExists();

        $this->mockFilesystem();
        $this->mockNovaRequestClassCall();

        config(['entity-generator.stubs.dump' => 'incorrect_stub']);

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusTest.php');
        $this->assertFileDoesNotExist('tests/fixtures/NovaWelcomeBonusTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/update_welcome_bonus_request.json');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of dump has been skipped cause the view incorrect_stub from the config entity-generator.stubs.dump is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testSuccess()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
        ]);

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mock
            ->shouldReceive('beginTransaction', 'rollBack')
            ->once();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockFilesystem();
        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/update_welcome_bonus_request.json');
    }

    public function testGenerateNovaPackageNotInstall()
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
