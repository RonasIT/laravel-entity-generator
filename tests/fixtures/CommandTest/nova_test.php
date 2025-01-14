<?php

namespace App\Tests;

use App\Models\Post;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;

class NovaPostTest extends TestCase
{
    use NovaTestTrait;

    protected static User $user;
    protected static ModelTestState $postState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);
        self::$postState ??= new ModelTestState(Post::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(Post::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_post_response.json', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('create_posts_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall(Post::class);

        $response->assertUnauthorized();

        self::$postState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(Post::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::$postState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(Post::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('update_posts_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(Post::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall(Post::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(Post::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response.json', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall(Post::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(Post::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('delete_posts_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(Post::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(Post::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(Post::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_post_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(Post::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall(Post::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(Post::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall(Post::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public static function getRunPostActionsData(): array
    {
        return [
        ];
    }

    #[DataProvider('getRunPostActionsData')]
    public function testRunPostActions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall(Post::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture($state, true);
    }

    public static function getPostActionsData(): array
    {
        return [
        ];
    }

    #[DataProvider('getPostActionsData')]
    public function testGetPostActions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall(Post::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function getPostFiltersData(): array
    {
        return [
        ];
    }

    #[DataProvider('getPostFiltersData')]
    public function testFilterPost(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall(Post::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
