<?php

namespace App\Tests;

use App\Models\WelcomeBonus;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Tests\NovaTestTraitTest;

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
        $data = $this->getJsonFixture('create_welcome_bonus_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('post', '/nova-api/welcome-bonus-resources', $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_welcome_bonus_response.json', $response->json());

        // TODO: Need to remove after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('create_welcome_bonuses_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->json('post', '/nova-api/welcome-bonus-resources');

        $response->assertUnauthorized();

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('post', '/nova-api/welcome-bonus-resources');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::$welcomeBonusState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/welcome-bonus-resources/1', $data);

        $response->assertNoContent();

        // TODO: Need to remove after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('update_welcome_bonuses_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_welcome_bonus_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/welcome-bonus-resources/0', $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->json('put', '/nova-api/welcome-bonus-resources/1');

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/welcome-bonus-resources/4');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('update_validation_response.json', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources/1/update-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('delete', '/nova-api/welcome-bonus-resources', [
            'resources' => [1, 2]
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture('delete_welcome_bonuses_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('delete', '/nova-api/welcome-bonus-resources', [
            'resources' => [0]
        ]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->json('delete', '/nova-api/welcome-bonus-resources', [
            'resources' => [1, 2]
        ]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_welcome_bonus_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources/0');

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->json('get', '/nova-api/welcome-bonus-resources/1');

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->json('get', '/nova-api/welcome-bonus-resources');

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources/creation-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRunWelcomeBonusActionsData(): array
    {
        return [
            [
                'action' => 'publish-post-action',
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_publish_post_action_state.json',
            ],
            [
                'action' => 'un-publish-post-action',
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_un_publish_post_action_state.json',
            ],
        ];
    }

    /**
     * @dataProvider getRunWelcomeBonusActionsData
     */
    public function testRunWelcomeBonusActions($action, $request, $welcome_bonusesStateFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('post', "/nova-api/welcome-bonus-resources/action?action={$action}", $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove after first successful start
        self::$welcomeBonusState->assertChangesEqualsFixture($welcome_bonusesStateFixture, true);
    }

    public function getWelcomeBonusActionsData(): array
    {
        return [
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_welcome_bonus_actions_publish_post_action.json',
            ],
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_welcome_bonus_actions_un_publish_post_action.json',
            ],
        ];
    }

    /**
     * @dataProvider getWelcomeBonusActionsData
     */
    public function testGetWelcomeBonusActions(array $request, string $responseFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources/actions', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }

    public function getWelcomeBonusFiltersData(): array
    {
        return [
            [
                'request' => [
                    'TextField:description_field' => $this->novaSearchParams(['search term']),
                ],
                'response_fixture' => 'filter_welcome_bonus_by_text_field.json',
            ],
            [
                'request' => [
                    'RonasIT\Support\Tests\Support\NovaTestGeneratorTest\CreatedAtFilter' => $this->novaSearchParams(['search term']),
                ],
                'response_fixture' => 'filter_welcome_bonus_by_created_at_filter.json',
            ],
        ];
    }

    /**
     * @dataProvider getWelcomeBonusFiltersData
     */
    public function testFilterWelcomeBonus(array $request, string $responseFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/welcome-bonus-resources', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
