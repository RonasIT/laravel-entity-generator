<?php

namespace App\Tests;

use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Tests\Support\Test\Models\Post;
use RonasIT\Support\Tests\Support\Test\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;

class PostTest extends TestCase
{
    protected static User $user;

    protected static ModelTestState $postState;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);

        self::$postState ??= new ModelTestState(Post::class);
    }

    public function testCreate()
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->actingAs(self::$user)->json('post', '/posts', $data);

        $response->assertCreated();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_post_response.json', $response->json(), true);

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('create_post_state.json', true);
    }

    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->json('post', '/posts', $data);

        $response->assertUnauthorized();
    }

    public function testUpdate()
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingAs(self::$user)->json('put', '/posts/1', $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('update_post_state.json', true);
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingAs(self::$user)->json('put', '/posts/0', $data);

        $response->assertNotFound();

        self::$postState->assertNotChanged();
    }

    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->json('put', '/posts/1', $data);

        $response->assertUnauthorized();

        self::$postState->assertNotChanged();
    }

    public function testDelete()
    {
        $response = $this->actingAs(self::$user)->json('delete', '/posts/1');

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::$postState->assertChangesEqualsFixture('delete_post_state.json', true);
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingAs(self::$user)->json('delete', '/posts/0');

        $response->assertNotFound();

        self::$postState->assertNotChanged();
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/posts/1');

        $response->assertUnauthorized();
    }

    public function testGet()
    {
        $response = $this->actingAs(self::$user)->json('get', '/posts/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson('get_post.json', $response->json());

        $this->assertEqualsFixture('get_post.json', $response->json());
    }

    public function testGetNotExists()
    {
        $response = $this->actingAs(self::$user)->json('get', '/posts/0');

        $response->assertNotFound();
    }

    public function testGetNoAuth()
    {
        $response = $this->json('get', '/posts/1');

        $response->assertUnauthorized();
    }

    public static function getSearchFilters(): array
    {
        return [
            [
                'filter' => ['all' => 1],
                'fixture' => 'search_all.json',
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 2,
                ],
                'fixture' => 'search_by_page_per_page.json',
            ],
        ];
    }

    #[DataProvider('getSearchFilters')]
    public function testSearch(array $filter, string $fixture)
    {
        $response = $this->actingAs(self::$user)->json('get', '/posts', $filter);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }

    public function testSearchNoAuth()
    {
        $response = $this->json('get', '/posts');

        $response->assertUnauthorized();
    }
}
