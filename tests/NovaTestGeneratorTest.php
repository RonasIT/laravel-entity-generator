<?php

namespace RonasIT\Support\Tests;

use App\Nova\Forum\PostResource;
use Carbon\Carbon;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Tests\Support\Command\Models\Post;
use RonasIT\Support\Tests\Support\Models\WelcomeBonus;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Exceptions\EntityCreateException;

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
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->getCommonNovaResourcesMock(),
        ]);

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create NovaPostResourceTest cause Post Nova resource does not exist. Create Post Nova resource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testGenerateToManyResources(): void
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->getCommonNovaResourcesMock([
                'BasePostResource',
                'PublishPostResource',
            ]),
        ]);

        $this->assertExceptionThrew(
            className: EntityCreateException::class,
            message: 'Cannot create NovaPostResourceTest cause was found a lot of suitable resources: BasePostResource, PublishPostResource. You may use --nova-resource-name option to specify a concrete resource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testGenerateNovaTestAlreadyExists()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockClass(NovaTestGenerator::class, [
            $this->classExistsMethodCall(['nova', 'NovaPostResourceTest']),
            $this->getCommonNovaResourcesMock([
                'PostResource',
            ]),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: "Cannot create NovaPostResourceTest cause it already exists. Remove app/Nova/NovaPostResourceTest.php and run command again.",
        );

        app(NovaTestGenerator::class)
            ->setModel('Post')
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
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockClass(NovaTestGenerator::class, [
            $this->getCommonNovaResourcesMock([
                'WelcomeBonusResource',
            ]),
        ]);

        $this->mockNovaRequestClassCall();

        $this->mockDBTransactionStartRollback();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertFileDoesNotExist('tests/NovaWelcomeBonusResourceTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusResourceTest/dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_resource_request.json');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of nova test has been skipped cause the view incorrect_stub from the config entity-generator.stubs.nova_test is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testDumpStubNotExist()
    {
        $this->mockNovaServiceProviderExists();

        $this->mockNovaRequestClassCall();

        config([
            'entity-generator.paths.models' => 'RonasIT/Support/Tests/Support/Models',
            'entity-generator.stubs.dump' => 'incorrect_stub',
        ]);

        $this->mockClass(NovaTestGenerator::class, [
            $this->getCommonNovaResourcesMock([
                'App\Nova\WelcomeBonusResource',
            ]),
        ]);

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusResourceTest.php');
        $this->assertFileDoesNotExist('tests/fixtures/NovaWelcomeBonusResourceTest/dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_resource_request.json');

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

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall([WelcomeBonus::class, true]),
        );

        $this->mockClass(NovaTestGenerator::class, [
            $this->getCommonNovaResourcesMock([
                'App\Nova\WelcomeBonusResource',
            ]),
        ]);

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusResourceTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusResourceTest/dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/create_welcome_bonus_resource_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusResourceTest/update_welcome_bonus_resource_request.json');
    }

    public function testCallCommandCreateNovateTestsWithResource()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT\Support\Tests\Support\Command\Models',
            'entity-generator.paths.factories' => 'RonasIT\Support\Tests\Support\Command\Factories',
        ]);

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall([PostResource::class, true]),
            $this->nativeClassExistsMethodCall([Post::class, true]),
        );

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('Post')
            ->setMetaData(['resource_name' => 'Forum/PostResource'])
            ->generate();

        $this->assertGeneratedFileEquals('created_forum_post_resource_test.php', 'tests/NovaPostResourceTest.php');
        $this->assertGeneratedFileEquals('dump_forum_post.sql', 'tests/fixtures/NovaPostResourceTest/dump.sql');
        $this->assertGeneratedFileEquals('create_forum_post_request.json', 'tests/fixtures/NovaPostResourceTest/create_post_resource_request.json');
        $this->assertGeneratedFileEquals('create_forum_post_response.json', 'tests/fixtures/NovaPostResourceTest/create_post_resource_response.json');
        $this->assertGeneratedFileEquals('update_forum_post_request.json', 'tests/fixtures/NovaPostResourceTest/update_post_resource_request.json');
    }

    public function testCallCommandCreateNovateTestsWithResourceNotFound()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT\Support\Tests\Support\Command\Models',
            'entity-generator.paths.factories' => 'RonasIT\Support\Tests\Support\Command\Factories',
        ]);

        Carbon::setTestNow('2016-10-20 11:05:00');

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall(['App\Nova\SomeResource', true], false),
        );

        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create NovaSomeResourceTest cause App\Nova\SomeResource does not exist. Create App\Nova\SomeResource.',
        );

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->setMetaData(['resource_name' => 'SomeResource'])
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
