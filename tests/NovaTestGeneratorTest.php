<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Tests\Support\Models\WelcomeBonus;
use RonasIT\Support\Exceptions\EntityCreateException;
use RonasIT\Support\Tests\Support\Models\Post;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestGeneratorMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testGenerateResourceNotExists()
    {
        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'News']),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\NewsResource"], false),
        );

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create NovaNewsResourceTest cause NewsResource Nova resource does not exist. Create NewsResource Nova resource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('News')
            ->setMetaData(['resource_name' => 'NewsResource'])
            ->generate();
    }

    public function testGenerateNovaTestAlreadyExists()
    {
        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['nova', 'NovaPostResourceTest']),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\Resources\PostResource"]),
        );

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: "Cannot create NovaPostResourceTest cause it's already exist. Remove NovaPostResourceTest.",
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->setMetaData(['resource_name' => 'Resources\PostResource'])
            ->generate();
    }

    public function testNovaTestStubNotExist()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
            'entity-generator.stubs.nova_test' => 'incorrect_stub',
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\WelcomeBonusResource"]),
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'WelcomeBonus']),
            $this->classExistsMethodCall(['nova', 'NovaWelcomeBonusResourceTest'], false),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
        ]);

        $this->mockNovaRequestClassCall();

        $this->mockDBTransactionStartRollback();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => 'WelcomeBonusResource'])
            ->generate();

        $this->assertFileDoesNotExist('tests/NovaWelcomeBonusTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusResourceTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_request.json');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of nova test has been skipped cause the view incorrect_stub from the config entity-generator.stubs.nova_test is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testDumpStubNotExist()
    {
        $this->mockNovaRequestClassCall();

        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
            'entity-generator.stubs.dump' => 'incorrect_stub',
        ]);

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'WelcomeBonus']),
            $this->classExistsMethodCall(['nova', 'NovaWelcomeBonusResourceTest'], false),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\WelcomeBonusResource"]),
        );

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => 'WelcomeBonusResource'])
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusResourceTest.php');
        $this->assertFileDoesNotExist('tests/fixtures/NovaWelcomeBonusResourceTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_request.json');

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

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'WelcomeBonus']),
            $this->classExistsMethodCall(['nova', 'NovaWelcomeBonusResourceTest'], false),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
        ]);

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\WelcomeBonusResource"]),
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => 'WelcomeBonusResource'])
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusResourceTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusResourceTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_request.json');
    }

    public function testWithManySameResources()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockNovaRequestClassCall();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'WelcomeBonus']),
        ]);

        $this->assertExceptionThrew(
            className: EntityCreateException::class,
            message: 'Cannot create NovaWelcomeBonusResource Test cause was found a lot of suitable resources: WelcomeBonusResource, Resources\WelcomeBonus Please, use --resource-name option',
        );

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => null])
            ->generate();
    }

    public function testSuccessWithoutSetMetaData()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
        ]);

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['nova', 'NovaPostResourceTest'], false),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'PostFactory'], false),
            $this->classExistsMethodCall(['factories', 'PostFactory'], false),
        ]);

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(["App\Nova\Resources\PostResource"]),
            $this->nativeClassExistsMethodCall([Post::class, true]),
        );

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->setMetaData(['resource_name' => null])
            ->generate();

        $this->assertGeneratedFileEquals('created_post_resource_test.php', 'tests/NovaPostResourceTest.php');
        $this->assertGeneratedFileEquals('post_dump.sql', 'tests/fixtures/NovaPostResourceTest/nova_post_dump.sql');
        $this->assertGeneratedFileEquals('create_post_request.json', 'tests/fixtures/NovaPostResourceTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_post_response.json', 'tests/fixtures/NovaPostResourceTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_post_request.json', 'tests/fixtures/NovaPostResourceTest/update_post_request.json');
    }

    public function testSuccessWithNestedFile(): void
    {
        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
        ]);

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['models', 'WelcomeBonus']),
            $this->classExistsMethodCall(['nova', 'NovaWelcomeBonusDraftResourceTest'], false),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
            $this->classExistsMethodCall(['factories', 'WelcomeBonusFactory'], false),
        ]);

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(['App\Nova\Resources\WelcomeBonusDraftResource']),
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => 'Resources\WelcomeBonusDraftResource'])
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusDraftResourceTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusDraftResourceTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusDraftResourceTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusDraftResourceTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusDraftResourceTest/update_welcome_bonus_request.json');
    }

    public function testSetIncorrectModel(): void
    {
        $this->mockNovaServiceProviderExists();

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create NovaSomeUndefinedModelResource Test cause SomeUndefinedModel does not exist. "
            . "Create a SomeUndefinedModel Model by himself or run command 'php artisan make:entity SomeUndefinedModel --only-model'.",
        );

        app(NovaTestGenerator::class)
            ->setModel('SomeUndefinedModel')
            ->setMetaData(['resource_name' => null])
            ->generate();
    }

    public function testGenerateNovaPackageNotInstall()
    {
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
