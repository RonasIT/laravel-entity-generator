namespace App\Tests;

use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
@if ($withAuth)
use App\Models\User;
@endif

class {{$entity}}Test extends TestCase
{
@if ($withAuth)
    protected $user;

@endif
    public function setUp() : void
    {
        parent::setUp();
@if ($withAuth)

        $this->user = User::find(1);
@endif
    }

    public function testCreate()
    {
        $data = $this->getJsonFixture('create_{{snake_case($entity)}}.json');

@if (!$withAuth)
        $response = $this->json('post', '/{{$entities}}', $data);
@else
        $response = $this->actingAs($this->user)->json('post', '/{{$entities}}', $data);
@endif

        $response->assertStatus(Response::HTTP_OK);

        $expect = Arr::except($data, ['id', 'updated_at', 'created_at']);
        $actual = Arr::except($response->json(), ['id', 'updated_at', 'created_at']);

        $this->assertEquals($expect, $actual);
        $this->assertDatabaseHas('{{$entities}}', $expect);
    }

@if ($withAuth)
    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_{{snake_case($entity)}}.json');

        $response = $this->json('post', '/{{$entities}}', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

@endif
    public function testUpdate()
    {
        $data = $this->getJsonFixture('update_{{snake_case($entity)}}.json');

@if (!$withAuth)
        $response = $this->json('put', '/{{$entities}}/1', $data);
@else
        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/1', $data);
@endif

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseHas('{{$entities}}', $data);
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_{{snake_case($entity)}}.json');

@if (!$withAuth)
        $response = $this->json('put', '/{{$entities}}/0', $data);
@else
        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/0', $data);
@endif

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

@if ($withAuth)
    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_{{snake_case($entity)}}.json');

        $response = $this->json('put', '/{{$entities}}/1', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

@endif
    public function testDelete()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/1');
@else
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/1');
@endif

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('{{$entities}}', [
            'id' => 1
        ]);
    }

    public function testDeleteNotExists()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/0');
@else
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/0');
@endif

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $this->assertDatabaseMissing('{{$entities}}', [
            'id' => 0
        ]);
    }

@if ($withAuth)
    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/{{$entities}}/1');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

@endif
    public function testGet()
    {
@if (!$withAuth)
        $response = $this->json('get', '/{{$entities}}/1');
@else
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/1');
@endif

        $response->assertStatus(Response::HTTP_OK);

        // TODO: Need to remove after first successful start
        $this->exportJson('get_{{\Illuminate\Support\Str::snake($entity)}}.json', $response->json());

        $this->assertEqualsFixture('get_{{\Illuminate\Support\Str::snake($entity)}}.json', $response->json());
    }

    public function testGetNotExists()
    {
@if (!$withAuth)
        $response = $this->json('get', '/{{$entities}}/0');
@else
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/0');
@endif

        $response->assertStatus(Response::HTTP_NOT_FOUND);
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
{{--
    Laravel inserts two spaces between @param and type, so we are forced
    to use hack here to preserve one space
--}}
@php
echo <<<PHPDOC
    /**
     * @dataProvider getSearchFilters
     *
     * @param array \$filter
     * @param string \$fixture
     */

PHPDOC;
@endphp
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/{{$entities}}', $filter);

        $response->assertStatus(Response::HTTP_OK);

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }
}
