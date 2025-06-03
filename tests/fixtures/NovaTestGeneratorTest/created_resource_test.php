<?php

namespace App\Tests;

use App\Models\WelcomeBonus;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;

class NovaWelcomeBonusTest extends TestCase
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
        $data = $this->getJsonFixture('create_welcome_bonus_request');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(WelcomeBonus::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_welcome_bonus_response', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('create_welcome_bonuses_state', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall(WelcomeBonus::class);

        $response->assertUnauthorized();

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall(WelcomeBonus::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response', $response->json(), true);

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonus::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('update_welcome_bonuses_state', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonus::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall(WelcomeBonus::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall(WelcomeBonus::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall(WelcomeBonus::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(WelcomeBonus::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('delete_welcome_bonuses_state', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(WelcomeBonus::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(WelcomeBonus::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(WelcomeBonus::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_welcome_bonus_response', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall(WelcomeBonus::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall(WelcomeBonus::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(WelcomeBonus::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall(WelcomeBonus::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response', $response->json(), true);
    }

    public static function getRunWelcomeBonusActionsData(): array
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

    #[DataProvider('getRunWelcomeBonusActionsData')]
    public function testRunWelcomeBonusActions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall(WelcomeBonus::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture($state, true);
    }

    public static function getWelcomeBonusActionsData(): array
    {
        return [
            [
                'resources' => [1, 2],
                'fixture' => 'get_welcome_bonus_actions_publish_post_action',
            ],
            [
                'resources' => [1, 2],
                'fixture' => 'get_welcome_bonus_actions_un_publish_post_action',
            ],
        ];
    }

    #[DataProvider('getWelcomeBonusActionsData')]
    public function testGetWelcomeBonusActions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall(WelcomeBonus::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function getWelcomeBonusFiltersData(): array
    {
        return [
            [
                'request' => [
                    'TextField:description_field' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_welcome_bonus_by_text_field',
            ],
            [
                'request' => [
                    'RonasIT\Support\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_welcome_bonus_by_created_at_filter',
            ],
        ];
    }

    #[DataProvider('getWelcomeBonusFiltersData')]
    public function testFilterWelcomeBonus(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall(WelcomeBonus::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
