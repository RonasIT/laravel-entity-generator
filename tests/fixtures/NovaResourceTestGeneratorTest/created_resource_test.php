<?php

namespace App\Tests;

use App\Models\Post;
use Illuminate\Support\Collection;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Traits\AuthTestTrait;

class PostTest extends TestCase
{
    use AuthTestTrait;

    protected $user;
    protected $postState:

    public function setUp(): void
    {
        parent::setUp();

        $this->user = 1;
        $this->postlState = new ModelTestState(Post::class);

        $this->skipDocumentationCollecting();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->postState);
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->actingViaSession($this->user)->json('post', '/nova-api/posts', $data);

        $response->assertCreated();
        $this->assertEqualsFixture('create_post_response.json', $response->json());

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $this->postState->getFixturePath('create_posts_state.json'),
            $this->postState->getChanges(),
            true
        );
    }

    public function testCreateNoAuth(): void
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->json('post', '/nova-api/posts', $data);

        $response->assertUnauthorized();

        $this->assertEquals(
            $this->postState->getExpectedEmptyState(),
            $this->postState->getChanges()
        );
}

    public function testCreateValidationError(): void
    {
        $modelState = new ModelTestState(Post::class);
        $response = $this->actingViaSession($this->user['id'])->json('post', '/nova-api/posts');

        $response->assertUnprocessable();

        $this->assertEquals(
            $this->postState->getExpectedEmptyState(),
            $this->postState->getChanges()
        );
    }

    public function testUpdate(): void
    {
        $modelState = new ModelTestState(Post::class);
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/posts/1', $data);

        $response->assertNoContent();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $this->postState->getFixturePath('update_posts_state.json'),
            $this->postState->getChanges(),
            true
        );
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/posts/0', $data);

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
        $response = $this->actingViaSession($this->user['id'])->json('put', '/nova-api/posts/4', []);

        $response->assertUnprocessable();
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingViaSession($this->user['id'])->json('get', '/nova-api/posts/1/update-fields');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $modelState = new ModelTestState(Post::class);
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/posts', [
            'resources' => [1, 2]
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $this->postState->getFixturePath('delete_posts_state.json'),
            $this->postState->getChanges(),
            true
        );
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/posts', [
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
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/posts/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_post_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/posts/0');

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->json('get', '/nova-api/posts/1');

        $response->assertUnauthorized();
    }

    public function testSearch(): void
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/posts', [
            'orderBy' => 'id',
            'orderByDirection' => 'asc'
        ]);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('search_posts_response.json', $response->json(), true);
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
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/posts/creation-fields');

        $response->assertStatus(Response::HTTP_OK);

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRunPostActionData(): array
    {
        return [
                    [
                'action' => '',
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run__state.json',
            ],
                    [
                'action' => '',
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run__state.json',
            ],
                ];
    }

    /**
     * @dataProvider  getRunPostActionData
     */
    public function testRunPostAction($action, $request, $postsStateFixture): void
    {
        $request['action'] = $action;
        $modelState = new ModelTestState(Post::class);
        $response = $this->actingViaSession($this->user)->json('post', "/nova-api/posts/action", $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());
        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $this->postState->getFixturePath($postsStateFixture),
            $this->postState->getChanges(),
            true
        );
    }

    public function getPostActionsData(): array
    {
        return [
                    [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_post_actions_.json',
            ],
                    [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_post_actions_.json',
            ],
                ];
    }

    /**
     * @dataProvider  getPostActionsData
     */
    public function testGetPostActions($request, $responseFixture): void
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/posts/actions', $request);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
