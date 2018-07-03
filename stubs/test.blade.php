namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;
@if ($withAuth)
use App\Models\User;
@endif

class {{$entity}}Test extends TestCase
{
@if ($withAuth)
    protected $user;

@endif
    public function setUp()
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

        $expect = array_except($data, ['id', 'updated_at', 'created_at']);
        $actual = array_except($response->json(), ['id', 'updated_at', 'created_at']);

        $this->assertEquals($expect, $actual);
    }

@if ($withAuth)
    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_{{snake_case($entity)}}.json');

        $response = $this->json('post', '/{{$entities}}', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
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

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
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
    }

    public function testDeleteNotExists()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/0');
@else
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/0');
@endif

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

@if ($withAuth)
    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/{{$entities}}/1');

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
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
        $this->exportJson($response->json(), 'get_{{snake_case($entity)}}.json');

        $this->assertEqualsFixture('get_{{snake_case($entity)}}.json', $response->json());
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
                'result' => 'search_by_all_{{snake_case($entity)}}.json'
            ],
            [
                'filter' => ['page' => 1],
                'result' => 'search_by_page_{{snake_case($entity)}}.json'
            ],
            [
                'filter' => ['per_page' => 1],
                'result' => 'search_by_per_page_{{snake_case($entity)}}.json'
            ],
        ];
    }

    /**
    * @dataProvider getSearchFilters
    *
    * @param array $filter
    * @param string $fixture
    */
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/{{$entities}}', $filter);

        // TODO: Need to remove after first successful start
        $this->exportJson($response->json(), $fixture);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }
}