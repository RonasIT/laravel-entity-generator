@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace App\Tests;

use Illuminate\Support\Collection;
use RonasIT\Support\Traits\AuthTestTrait;
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
@endif

class {{$entity}}Test extends TestCase
{
    use AuthTestTrait;

    /**
     * @var Collection
     */
    protected static $origin{{$entities}}State;
    protected $user;

    public function setUp() : void
    {
        parent::setUp();

        $this->user = 1;

        $this->skipDocumentationCollecting();

        self::$origin{{$entities}}State = self::$origin{{$entities}}State ?? $this->getDataSet('{{$lower_entities}}');
    }

    public function testCreate()
    {
        $data = $this->getJsonFixture('create_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('post', '/nova-api/{{$url_path}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated();
@endif
        $this->assertEqualsFixture('create_{{$lower_entity}}_response.json', $response->json());
        $this->assertChangesEqualsFixture('{{$lower_entities}}', 'create_{{$lower_entities}}_state.json', self::$origin{{$entities}}State);
    }

    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_{{$lower_entity}}_request.json');

        $response = $this->json('post', '/nova-api/{{$url_path}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testCreateValidation(): void
    {
        $response = $this->novaActingAs($this->user['id'])->json('post', '/nova-api/{{$url_path}}', []);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif

        $this->assertNoChanges('{{$lower_entity}}', self::$origin{{$entities}}State);
    }

    public function testUpdate()
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/{{$url_path}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/{{$url_path}}/0', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->json('put', '/nova-api/{{$url_path}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testDelete()
    {
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/{{$url_path}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/{{$url_path}}/0');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/nova-api/{{$url_path}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testGet()
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->exportJson('get_{{$lower_entity}}.json', $response->json());

        $this->assertEqualsFixture('get_{{$lower_entity}}.json', $response->json());
    }

    public function testGetNotExists()
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/0');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
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
     * @dataProvider getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/nova-api/{{$url_path}}', $filter);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }
}
