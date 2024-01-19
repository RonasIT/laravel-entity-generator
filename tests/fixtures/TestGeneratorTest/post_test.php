<?php

namespace App\Tests;

use App\Models\User;

class PostTest extends TestCase
{
    protected $user;

    public function setUp() : void
    {
        parent::setUp();

        $this->user = User::find(1);
    }

    public function testCreate()
    {
        $data = $this->getJsonFixture('create_post_request.json');

        $response = $this->actingAs($this->user)->json('post', '/posts', $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_post_response.json', $response->json());

        $this->assertDatabaseHas('posts', $this->getJsonFixture('create_post_response.json'));
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

        $response = $this->actingAs($this->user)->json('put', '/posts/1', $data);

        $response->assertNoContent();

        $this->assertDatabaseHas('posts', $data);
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->actingAs($this->user)->json('put', '/posts/0', $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_post_request.json');

        $response = $this->json('put', '/posts/1', $data);

        $response->assertUnauthorized();
    }

    public function testDelete()
    {
        $response = $this->actingAs($this->user)->json('delete', '/posts/1');

        $response->assertNoContent();

        $this->assertDatabaseMissing('posts', [
            'id' => 1
        ]);
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingAs($this->user)->json('delete', '/posts/0');

        $response->assertNotFound();

        $this->assertDatabaseMissing('posts', [
            'id' => 0
        ]);
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/posts/1');

        $response->assertUnauthorized();
    }

    public function testGet()
    {
        $response = $this->actingAs($this->user)->json('get', '/posts/1');

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson('get_post.json', $response->json());

        $this->assertEqualsFixture('get_post.json', $response->json());
    }

    public function testGetNotExists()
    {
        $response = $this->actingAs($this->user)->json('get', '/posts/0');

        $response->assertNotFound();
    }

    public function getSearchFilters()
    {
        return [
            [
                'filter' => ['all' => 1],
                'result' => 'search_all.json'
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 2
                ],
                'result' => 'search_by_page_per_page.json'
            ],
        ];
    }

    /**
     * @dataProvider  getSearchFilters
     *
     * @param  array $filter
     * @param  string $fixture
     */
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/posts', $filter);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }

}
