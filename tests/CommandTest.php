<?php

namespace RonasIT\Support\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Tests\Support\Command\CommandMockTrait;
use UnexpectedValueException;

class CommandTest extends TestCase
{
    use CommandMockTrait;

    public function testCallWithInvalidCrudOption()
    {
        $this->assertExceptionThrew(
            className: UnexpectedValueException::class,
            message: 'Invalid method T',
        );

        $this->artisan('make:entity Post --methods=T');
    }

    public function testCallWithMissingModelService()
    {
        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: 'Cannot create API without entity.',
        );

        $this->artisan('make:entity Post --only-api');
    }

    public function testCallCommand()
    {
        config([
            'entity-generator.paths.models' => 'RonasIT\Support\Tests\Support\Command\Models',
            'entity-generator.paths.factories' => 'RonasIT\Support\Tests\Support\Command\Factories',
        ]);

        Carbon::setTestNow('2016-10-20 11:05:00');

        $this->mockFilesystem();
        $this->mockGenerator();
        $this->mockGettingModelInstance();

        $this
            ->artisan('make:entity Post --methods=CRUD')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('migration.php', 'database/migrations/2016_10_20_110500_posts_create_table.php');
        $this->assertGeneratedFileEquals('factory.php', 'RonasIT/Support/Tests/Support/Command/Factories/PostFactory.php');
        $this->assertGeneratedFileEquals('seeder.php', 'database/seeders/PostSeeder.php');
        $this->assertGeneratedFileEquals('model.php', 'RonasIT/Support/Tests/Support/Command/Models/Post.php');
        $this->assertGeneratedFileEquals('repository.php', 'app/Repositories/PostRepository.php');
        $this->assertGeneratedFileEquals('service.php', 'app/Services/PostService.php');
        $this->assertGeneratedFileEquals('create_request.php', 'app/Http/Requests/Post/CreatePostRequest.php');
        $this->assertGeneratedFileEquals('get_request.php', 'app/Http/Requests/Post/GetPostRequest.php');
        $this->assertGeneratedFileEquals('search_request.php', 'app/Http/Requests/Post/SearchPostsRequest.php');
        $this->assertGeneratedFileEquals('update_request.php', 'app/Http/Requests/Post/UpdatePostRequest.php');
        $this->assertGeneratedFileEquals('delete_request.php', 'app/Http/Requests/Post/DeletePostRequest.php');
        $this->assertGeneratedFileEquals('controller.php', 'app/Http/Controllers/PostController.php');
        $this->assertGeneratedFileEquals('resource.php', 'app/Http/Resources/Post/PostResource.php');
        $this->assertGeneratedFileEquals('resource_collection.php', 'app/Http/Resources/Post/PostsCollectionResource.php');
        $this->assertGeneratedFileEquals('routes.php', 'routes/api.php');
        $this->assertGeneratedFileEquals('test.php', 'tests/PostTest.php');
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/PostTest/dump.sql');
        $this->assertGeneratedFileEquals('create_request.json', 'tests/fixtures/PostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_response.json', 'tests/fixtures/PostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_request.json', 'tests/fixtures/PostTest/update_post_request.json');
        $this->assertGeneratedFileEquals('validation.php', 'lang/en/validation.php');
        $this->assertGeneratedFileEquals('nova_resource.php', 'app/Nova/PostResource.php');
        $this->assertGeneratedFileEquals('nova_test.php', 'tests/NovaPostTest.php');
        $this->assertGeneratedFileEquals('nova_dump.php', 'tests/fixtures/NovaPostTest/nova_post_dump.sql');
        $this->assertGeneratedFileEquals('create_request.json', 'tests/fixtures/NovaPostTest/create_post_request.json');
        $this->assertGeneratedFileEquals('create_response.json', 'tests/fixtures/NovaPostTest/create_post_response.json');
        $this->assertGeneratedFileEquals('update_request.json', 'tests/fixtures/NovaPostTest/update_post_request.json');
    }

    public function testMakeOnly()
    {
        $this->mockFilesystemPostModelExists();

        $this
            ->artisan('make:entity Post --methods=CRUD --only-repository')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('make_only_repository.php', 'app/Repositories/PostRepository.php');
        $this->assertFileDoesNotExist('database/migrations/2016_10_20_110500_posts_create_table.php');
        $this->assertFileDoesNotExist('database/factories/PostFactory.php');
        $this->assertFileDoesNotExist('database/seeders/PostSeeder.php');
        $this->assertFileDoesNotExist('app/Models/Post.php');
        $this->assertFileDoesNotExist('app/Services/PostService.php');
        $this->assertFileDoesNotExist('app/Http/Requests/Post/CreatePostRequest.php');
        $this->assertFileDoesNotExist('app/Http/Requests/Post/GetPostRequest.php');
        $this->assertFileDoesNotExist('app/Http/Requests/Post/SearchPostsRequest.php');
        $this->assertFileDoesNotExist('app/Http/Requests/Post/UpdatePostRequest.php');
        $this->assertFileDoesNotExist('app/Http/Requests/Post/DeletePostRequest.php');
        $this->assertFileDoesNotExist('app/Http/Controllers/PostController.php');
        $this->assertFileDoesNotExist('app/Http/Resources/Post/PostResource.php');
        $this->assertFileDoesNotExist('app/Http/Resources/Post/PostsCollectionResource.php');
        $this->assertFileDoesNotExist('routes/api.php');
        $this->assertFileDoesNotExist('tests/PostTest.php');
        $this->assertFileDoesNotExist('tests/fixtures/PostTest/dump.sql');
        $this->assertFileDoesNotExist('tests/fixtures/PostTest/create_post_request.json');
        $this->assertFileDoesNotExist('tests/fixtures/PostTest/create_post_response.json');
        $this->assertFileDoesNotExist('tests/fixtures/PostTest/update_post_request.json');
        $this->assertFileDoesNotExist('lang/en/validation.php');
        $this->assertFileDoesNotExist('app/Nova/PostResource.php');
        $this->assertFileDoesNotExist('tests/NovaPostTest.php');
        $this->assertFileDoesNotExist('tests/fixtures/NovaPostTest/nova_post_dump.sql');
        $this->assertFileDoesNotExist('tests/fixtures/NovaPostTest/create_post_request.json');
        $this->assertFileDoesNotExist('tests/fixtures/NovaPostTest/create_post_response.json');
        $this->assertFileDoesNotExist('tests/fixtures/NovaPostTest/update_post_request.json');
    }

    public function testCallWithNotDefaultConfig()
    {
        $rootUrl = vfsStream::setup('root', null, [
            'config' => [
                'entity-generator.php' => "<?php return ['test' => 'original'];",
            ],
            'routes' => [
                'api.php' => "",
            ],
        ])->url();

        $this->app->instance('path.base', $rootUrl);

        Config::set('entity-generator', ['test' => 'changed']);

        $this->artisan('make:entity Post')
            ->expectsOutput('Config has been updated')
            ->assertExitCode(0);

        $configPath = $rootUrl . '/config/entity-generator.php';

        $updated = include $configPath;

        $this->assertTrue(file_exists($configPath));
        
        $this->assertEquals(array_merge(['test' => 'changed'], config('entity-generator')), $updated);
    }
}
