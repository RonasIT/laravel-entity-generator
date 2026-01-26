<?php

namespace App\Tests;

use RonasIT\Support\Tests\Support\Command\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;
use App\Nova\AdminResource;

class NovaAdminResourceTest extends TestCase
{
    use NovaTestTrait;

    protected static User $user;
    protected static ModelTestState $userState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);
        self::$userState ??= new ModelTestState(User::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_admin_resource_request');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(AdminResource::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_admin_resource_response', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::$userState->assertChangesEqualsFixture('create_users_state', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall(AdminResource::class);

        $response->assertUnauthorized();

        self::$userState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(AdminResource::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response', $response->json(), true);

        self::$userState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_admin_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(AdminResource::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$userState->assertChangesEqualsFixture('update_users_state', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_admin_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(AdminResource::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall(AdminResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(AdminResource::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall(AdminResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(AdminResource::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::$userState->assertChangesEqualsFixture('delete_users_state', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(AdminResource::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(AdminResource::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(AdminResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_admin_resource_response', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(AdminResource::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall(AdminResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(AdminResource::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall(AdminResource::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response', $response->json(), true);
    }

    public static function getRunAdminResourceActionsData(): array
    {
        return [
            [
                'action' => PublishPostAction::class,
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_publish_post_action_state',
            ],
            [
                'action' => UnPublishPostAction::class,
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_un_publish_post_action_state',
            ],
        ];
    }

    #[DataProvider('getRunAdminResourceActionsData')]
    public function testRunAdminResourceActions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall(AdminResource::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::$userState->assertChangesEqualsFixture($state, true);
    }

    public static function getAdminResourceActionsData(): array
    {
        return [
            [
                'resources' => [1, 2],
                'fixture' => 'get_admin_resource_actions_publish_post_action',
            ],
            [
                'resources' => [1, 2],
                'fixture' => 'get_admin_resource_actions_un_publish_post_action',
            ],
        ];
    }

    #[DataProvider('getAdminResourceActionsData')]
    public function testGetAdminResourceActions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall(AdminResource::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function getAdminResourceFiltersData(): array
    {
        return [
            [
                'request' => [
                    'TextField:description_field' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_admin_resource_by_text_field',
            ],
            [
                'request' => [
                    'RonasIT\Support\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_admin_resource_by_created_at_filter',
            ],
        ];
    }

    #[DataProvider('getAdminResourceFiltersData')]
    public function testFilterAdminResource(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall(AdminResource::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
