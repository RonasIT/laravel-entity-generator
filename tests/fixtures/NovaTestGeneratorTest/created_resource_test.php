<?php

namespace App\Tests;

use App\Models\SomePost;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Tests\NovaTestTraitTest;

class NovaSomePostTest extends TestCase
{
    use NovaTestTrait;

    protected static User $user;
    protected static ModelTestState $somePostState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);
        self::$somePostState ??= new ModelTestState(SomePost::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_some_post_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('post', '/nova-api/some-post-resources', $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_some_post_response.json', $response->json());

        // TODO: Need to remove after first successful start
        self::$somePostState->assertChangesEqualsFixture('create_some_posts_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $data = $this->getJsonFixture('create_some_post_request.json');

        $response = $this->json('post', '/nova-api/some-post-resources', $data);

        $response->assertUnauthorized();

        self::$somePostState->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('post', '/nova-api/some-post-resources');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::$somePostState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_some_post_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/some-post-resources/1', $data);

        $response->assertNoContent();

        // TODO: Need to remove after first successful start
        self::$somePostState->assertChangesEqualsFixture('update_some_posts_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_some_post_request.json');

        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/some-post-resources/0', $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $data = $this->getJsonFixture('update_some_post_request.json');

        $response = $this->json('put', '/nova-api/some-post-resources/1', $data);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('put', '/nova-api/some-post-resources/4');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('update_validation_response.json', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources/1/update-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('delete', '/nova-api/some-post-resources', [
            'resources' => [1, 2]
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        self::$somePostState->assertChangesEqualsFixture('delete_some_posts_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('delete', '/nova-api/some-post-resources', [
            'resources' => [0]
        ]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->json('delete', '/nova-api/some-post-resources', [
            'resources' => [1, 2]
        ]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_some_post_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources/0');

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->json('get', '/nova-api/some-post-resources/1');

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->json('get', '/nova-api/some-post-resources', [
            'orderBy' => 'id',
            'orderByDirection' => 'asc'
        ]);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources/creation-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRunSomePostActionsData(): array
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
     * @dataProvider getRunSomePostActionsData
     */
    public function testRunSomePostActions($action, $request, $some_postsStateFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('post', "/nova-api/some-post-resources/action?action={$action}", $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove after first successful start
        self::$somePostState->assertChangesEqualsFixture($some_postsStateFixture, true);
    }

    public function getSomePostActionsData(): array
    {
        return [
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_some_post_actions_publish_post_action.json',
            ],
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_some_post_actions_un_publish_post_action.json',
            ],
        ];
    }

    /**
     * @dataProvider getSomePostActionsData
     */
    public function testGetSomePostActions(array $request, string $responseFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources/actions', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }

    public function getSomePostFiltersData(): array
    {
        return [
            [
                'request' => [
                    'TextField:description_field' => $this->novaSearchParams(['search term']),
                ],
                'response_fixture' => 'filter_some_post_by_text_field.json',
            ],
            [
                'request' => [
                    'RonasIT\Support\Tests\Support\CreatedAtFilter' => $this->novaSearchParams(['search term']),
                ],
                'response_fixture' => 'filter_some_post_by_created_at_filter.json',
            ],
        ];
    }

    /**
     * @dataProvider getSomePostFiltersData
     */
    public function testFilterSomePost(array $request, string $responseFixture): void
    {
        $response = $this->actingAs(self::$user, 'web')->json('get', '/nova-api/some-post-resources', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
