<?php

namespace RonasIT\Support\Tests;

use App\Nova\AdminResource;
use Carbon\Carbon;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Tests\Support\Command\Models\User;
use RonasIT\Support\Tests\Support\Models\WelcomeBonus;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Exceptions\EntityCreateException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;
use RonasIT\Support\Tests\Support\NovaTestGeneratorTest\NovaTestGeneratorMockTrait;

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
            className: ResourceNotExistsException::class,
            message: 'Cannot create NovaPostResourceTest cause Post Nova resource does not exist. Create Post Nova resource and run command again.',
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

    public function testCallCommandCreateNovaTestsWithResource()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT\Support\Tests\Support\Command\Models',
            'entity-generator.paths.factories' => 'RonasIT\Support\Tests\Support\Command\Factories',
        ]);

        $this->mockDBTransactionStartRollback();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true]),
            $this->nativeClassExistsMethodCall([AdminResource::class, true]),
            $this->nativeClassExistsMethodCall([User::class, true]),
        );

        $this->mockNovaRequestClassCall();

        app(NovaTestGenerator::class)
            ->setModel('User')
            ->setNovaResource('AdminResource')
            ->generate();

        $this->assertGeneratedFileEquals('created_admin_resource_test.php', 'tests/NovaAdminResourceTest.php');
        $this->assertGeneratedFileEquals('dump_admin.sql', 'tests/fixtures/NovaAdminResourceTest/dump.sql');
        $this->assertGeneratedFileEquals('create_admin_request.json', 'tests/fixtures/NovaAdminResourceTest/create_admin_resource_request.json');
        $this->assertGeneratedFileEquals('create_admin_response.json', 'tests/fixtures/NovaAdminResourceTest/create_admin_resource_response.json');
        $this->assertGeneratedFileEquals('update_admin_request.json', 'tests/fixtures/NovaAdminResourceTest/update_admin_resource_request.json');
    }

    public function testCallCommandCreateNovaTestsWithResourceNotFound()
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
            ->setNovaResource('SomeResource')
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
