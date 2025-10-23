<?php

namespace App\Tests;

use RonasIT\Support\Tests\Support\Command\Models\Post;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;
use App\Nova\PostResource;
use RonasIT\Support\Tests\Support\Command\Models\User;

class NovaPostResourceTest extends TestCase
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
        $data = $this->getJsonFixture('create_post_resource_request');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(PostResource::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_post_resource_response', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('create_posts_state', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall(PostResource::class);

        $response->assertUnauthorized();

        self::$postState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(PostResource::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response', $response->json(), true);

        self::$postState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_post_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(PostResource::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('update_posts_state', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_post_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(PostResource::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall(PostResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(PostResource::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall(PostResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(PostResource::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('delete_posts_state', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(PostResource::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(PostResource::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(PostResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_post_resource_response', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(PostResource::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall(PostResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(PostResource::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall(PostResource::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response', $response->json(), true);
    }

    public static function getRunPostResourceActionsData(): array
    {
        return [
        ];
    }

    #[DataProvider('getRunPostResourceActionsData')]
    public function testRunPostResourceActions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall(PostResource::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture($state, true);
    }

    public static function getPostResourceActionsData(): array
    {
        return [
        ];
    }

    #[DataProvider('getPostResourceActionsData')]
    public function testGetPostResourceActions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall(PostResource::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function getPostResourceFiltersData(): array
    {
        return [
        ];
    }

    #[DataProvider('getPostResourceFiltersData')]
    public function testFilterPostResource(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall(PostResource::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
