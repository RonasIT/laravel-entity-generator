<?php

namespace App\Tests;

use App\Models\Post;
use Illuminate\Support\Collection;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Traits\AuthTestTrait;

class PostTest extends TestCase
{
    use AuthTestTrait;

    protected static $user;
    protected static $postState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user = 1;
        self::$postState ??= new ModelTestState(Post::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->actingViaSession(self::$user)->json('post', '/nova-api/posts', $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_post_response.json', $response->json());

        // TODO: Need to remove after first successful start
        self::$postState->assertChangesEqualsFixture('create_posts_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->json('post', '/nova-api/posts', $data);

        $response->assertUnauthorized();

        self::$postState->assertNotChanged();
}

    public function testCreateValidationError(): void
    {
        $response = $this->actingViaSession(self::$user)->json('post', '/nova-api/posts');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::$postState->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/posts/1', $data);

        $response->assertNoContent();

        // TODO: Need to remove after first successful start
        self::$postState->assertChangesEqualsFixture('update_posts_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/posts/0', $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->json('put', '/nova-api/posts/1', $data);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/posts/4');

        $response->assertUnprocessable();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('update_validation_response.json', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts/1/update-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->actingViaSession(self::$user)->json('delete', '/nova-api/posts', [
            'resources' => [1, 2]
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        self::$postState->assertChangesEqualsFixture('delete_posts_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingViaSession(self::$user)->json('delete', '/nova-api/posts', [
            'resources' => [0]
        ]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->json('delete', '/nova-api/posts', [
            'resources' => [1, 2]
        ]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_post_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts/0');

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->json('get', '/nova-api/posts/1');

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->json('get', '/nova-api/posts', [
            'orderBy' => 'id',
            'orderByDirection' => 'asc'
        ]);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts/creation-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRunPostActionsData(): array
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
     * @dataProvider  getRunPostActionsData
     */
    public function testRunPostActions($action, $request, $postsStateFixture): void
    {
        $request['action'] = $action;
        $response = $this->actingViaSession(self::$user)->json('post', "/nova-api/posts/action", $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove after first successful start
        self::$postState->assertChangesEqualsFixture($postsStateFixture, true);
    }

    public function getPostActionsData(): array
    {
        return [
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_post_actions_publish_post_action.json',
            ],
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_post_actions_un_publish_post_action.json',
            ],
        ];
    }

    /**
     * @dataProvider  getPostActionsData
     */
    public function testGetPostActions(array $request, string $responseFixture): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts/actions', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }

    public function getPostFiltersData(): array
    {
        return [
            [
                'filters' => [
                    'TextField:description_field' => 'search term',
                ],
                'response_fixture' => 'filter_post_by_text_field.json',
            ],
            [
                'filters' => [
                    'RonasIT\Support\Tests\Support\CreatedAtFilter' => 'search term',
                ],
                'response_fixture' => 'filter_post_by_created_at_filter.json',
            ],
        ];
    }

    /**
     * @dataProvider  getPostFiltersData
     */
    public function testFilterPost(array $filters, string $responseFixture): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/posts', [
            'filters' => base64_encode(json_encode($filters))
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
