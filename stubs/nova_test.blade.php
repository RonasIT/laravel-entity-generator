@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace App\Tests;

use App\Models\{{$entity}};
use Illuminate\Support\Collection;
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Traits\AuthTestTrait;
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
@endif

class {{$entity}}Test extends TestCase
{
    use AuthTestTrait;

    protected static $user;
    protected static ${{$lower_entity}}State;

    public function setUp(): void
    {
        parent::setUp();

        self::$user = 1;
        self::${{$lower_entity}}State ??= new ModelTestState({{$entity}}::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession(self::$user)->json('post', '/nova-api/{{$url_path}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated();
@endif

        $this->assertEqualsFixture('create_{{$lower_entity}}_response.json', $response->json());

        // TODO: Need to remove after first successful start
        self::${{$lower_entity}}State->assertChangesEqualsFixture('create_{{$lower_entities}}_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $data = $this->getJsonFixture('create_{{$lower_entity}}_request.json');

        $response = $this->json('post', '/nova-api/{{$url_path}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif

        self::${{$lower_entity}}State->assertNotChanged();
}

    public function testCreateValidationError(): void
    {
        $response = $this->actingViaSession(self::$user)->json('post', '/nova-api/{{$url_path}}');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::${{$lower_entity}}State->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/{{$url_path}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif

        // TODO: Need to remove after first successful start
        self::${{$lower_entity}}State->assertChangesEqualsFixture('update_{{$lower_entities}}_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/{{$url_path}}/0', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testUpdateNoAuth(): void
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->json('put', '/nova-api/{{$url_path}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->actingViaSession(self::$user)->json('put', '/nova-api/{{$url_path}}/4');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('update_validation_response.json', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}/1/update-fields');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response.json', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->actingViaSession(self::$user)->json('delete', '/nova-api/{{$url_path}}', [
            'resources' => [1, 2]
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        self::${{$lower_entity}}State->assertChangesEqualsFixture('delete_{{$lower_entities}}_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingViaSession(self::$user)->json('delete', '/nova-api/{{$url_path}}', [
            'resources' => [0]
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->json('delete', '/nova-api/{{$url_path}}', [
            'resources' => [1, 2]
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testGet(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_{{$lower_entity}}_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}/0');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testGetNoAuth(): void
    {
        $response = $this->json('get', '/nova-api/{{$url_path}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->json('get', '/nova-api/{{$url_path}}', [
            'orderBy' => 'id',
            'orderByDirection' => 'asc'
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}/creation-fields');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRun{{$entity}}ActionsData(): array
    {
        return [
@foreach($actions as $action)
            [
                'action' => '{{$action['url']}}',
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_{{$action['fixture']}}_state.json',
            ],
@endforeach
        ];
    }

    /**
     * @dataProvider getRun{{$entity}}ActionsData
     */
    public function testRun{{$entity}}Actions($action, $request, ${{$lower_entities}}StateFixture): void
    {
        $request['action'] = $action;
        $response = $this->actingViaSession(self::$user)->json('post', "/nova-api/{{$url_path}}/action", $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove after first successful start
        self::${{$lower_entity}}State->assertChangesEqualsFixture(${{$lower_entities}}StateFixture, true);
    }

    public function get{{$entity}}ActionsData(): array
    {
        return [
@foreach($actions as $action)
            [
                'request' => [
                    'resources' => '1,2',
                ],
                'response_fixture' => 'get_{{$lower_entity}}_actions_{{$action['fixture']}}.json',
            ],
@endforeach
        ];
    }

    /**
     * @dataProvider get{{$entity}}ActionsData
     */
    public function testGet{{$entity}}Actions(array $request, string $responseFixture): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}/actions', $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }

    public function get{{$entity}}FiltersData(): array
    {
        return [
@foreach($filters as $filter)
            [
                'filters' => [
                    '{{$filter['name']}}' => 'search term',
                ],
                'response_fixture' => 'filter_{{$lower_entity}}_by_{{$filter['fixture_name']}}.json',
            ],
@endforeach
        ];
    }

    /**
     * @dataProvider get{{$entity}}FiltersData
     */
    public function testFilter{{$entity}}(array $filters, string $responseFixture): void
    {
        $response = $this->actingViaSession(self::$user)->json('get', '/nova-api/{{$url_path}}', [
            'filters' => base64_encode(json_encode($filters))
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
