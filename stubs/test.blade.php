namespace App\Tests;

use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class {{$entity}}Test extends TestCase
{
    protected $user;

    public function setUp() {
        parent::setUp();

        $this->user = User::find(1);
    }

    public function testCreate() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->actingAs($this->user)->json('post', '/{{$entities}}', $data);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testCreateCheckResponse() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->actingAs($this->user)->json('post', '/{{$entities}}', $data);

        $expect = array_except($data, ['id', 'updated_at', 'created_at']);
        $actual = array_except($response->json(), ['id', 'updated_at', 'created_at']);

        $this->assertEquals($expect, $actual);
    }

    public function testCreateNoAuth() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->json('post', '/{{$entities}}', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testUpdate() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/1', $data);

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testUpdateNotExists() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/0', $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateNoAuth() {
        $data = $this->getJsonFixture('{{strtolower($entity)}}.json');

        $response = $this->json('put', '/{{$entities}}/1', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testDelete() {
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/1');

        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function testDeleteNotExists() {
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/2');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteNoAuth() {
        $response = $this->json('delete', '/{{$entities}}/1');

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testGet() {
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/1');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function testGetCheckResponse() {
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/1');

        $filteredResponse = array_except($response->json(), ['created_at', 'updated_at']);

        $this->assertEqualsFixture('{{strtolower($entity)}}.json', $filteredResponse);
    }

    public function testGetNotExists() {
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/2');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function getSearchFilters() {
        return [
            // TODO: Need to add search filters
        ];
    }

    /**
     * @dataProvider getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearch($filter, $fixture) {
        $response = $this->json('get', '/{{$entities}}', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }
}