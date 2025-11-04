<?php

namespace App\Tests;

use Models\WelcomeBonus;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;
use App\Nova\Resources\WelcomeBonusDraftResource;
use Models\User;

class NovaWelcomeBonusDraftResourceTest extends TestCase
{
    use NovaTestTrait;

    protected static User $user;
    protected static ModelTestState $welcomeBonusState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);
        self::$welcomeBonusState ??= new ModelTestState(WelcomeBonus::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_welcome_bonus_draft_resource_request');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(WelcomeBonusDraftResource::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_welcome_bonus_draft_resource_response', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('create_welcome_bonuses_state', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall(WelcomeBonusDraftResource::class);

        $response->assertUnauthorized();

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(WelcomeBonusDraftResource::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response', $response->json(), true);

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_draft_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonusDraftResource::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('update_welcome_bonuses_state', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_draft_resource_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonusDraftResource::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall(WelcomeBonusDraftResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonusDraftResource::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall(WelcomeBonusDraftResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(WelcomeBonusDraftResource::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('delete_welcome_bonuses_state', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(WelcomeBonusDraftResource::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(WelcomeBonusDraftResource::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(WelcomeBonusDraftResource::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_welcome_bonus_draft_resource_response', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(WelcomeBonusDraftResource::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall(WelcomeBonusDraftResource::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(WelcomeBonusDraftResource::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall(WelcomeBonusDraftResource::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response', $response->json(), true);
    }

    public static function getRunWelcomeBonusDraftResourceActionsData(): array
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

    #[DataProvider('getRunWelcomeBonusDraftResourceActionsData')]
    public function testRunWelcomeBonusDraftResourceActions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall(WelcomeBonusDraftResource::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture($state, true);
    }

    public static function getWelcomeBonusDraftResourceActionsData(): array
    {
        return [
            [
                'resources' => [1, 2],
                'fixture' => 'get_welcome_bonus_draft_resource_actions_publish_post_action',
            ],
            [
                'resources' => [1, 2],
                'fixture' => 'get_welcome_bonus_draft_resource_actions_un_publish_post_action',
            ],
        ];
    }

    #[DataProvider('getWelcomeBonusDraftResourceActionsData')]
    public function testGetWelcomeBonusDraftResourceActions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall(WelcomeBonusDraftResource::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function getWelcomeBonusDraftResourceFiltersData(): array
    {
        return [
            [
                'request' => [
                    'TextField:description_field' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_welcome_bonus_draft_resource_by_text_field',
            ],
            [
                'request' => [
                    'RonasIT\Support\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_welcome_bonus_draft_resource_by_created_at_filter',
            ],
        ];
    }

    #[DataProvider('getWelcomeBonusDraftResourceFiltersData')]
    public function testFilterWelcomeBonusDraftResource(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall(WelcomeBonusDraftResource::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
