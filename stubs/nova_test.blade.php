@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace App\Tests;

use App\Models\{{$entity}};
use RonasIT\Support\Tests\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
@endif

class Nova{{$entity}}Test extends TestCase
{
    use NovaTestTrait;

    protected static User $user;
    protected static ModelTestState ${{$dromedary_entity}}State;

    public function setUp(): void
    {
        parent::setUp();

        self::$user ??= User::find(1);
        self::${{$dromedary_entity}}State ??= new ModelTestState({{$entity}}::class);

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $data = $this->getJsonFixture('create_{{$snake_entity}}_request.json');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall({{$entity}}::class, $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated();
@endif

        $this->assertEqualsFixture('create_{{$snake_entity}}_response.json', $response->json());

        // TODO: Need to remove after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('create_{{$lower_entities}}_state.json', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall({{$entity}}::class);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif

        self::${{$dromedary_entity}}State->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall({{$entity}}::class);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('create_validation_response.json', $response->json(), true);

        self::${{$dromedary_entity}}State->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_{{$snake_entity}}_request.json');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 1, $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif

        // TODO: Need to remove after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('update_{{$lower_entities}}_state.json', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_{{$snake_entity}}_request.json');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 0, $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall({{$entity}}::class, 1);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 4);

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
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall({{$entity}}::class, 1);

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
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(
            resourceClass: {{$entity}}::class,
            resourceIds: [1, 2],
        );

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('delete_{{$lower_entities}}_state.json', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall(
            resourceClass: {{$entity}}::class,
            resourceIds: [0],
        );

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall(
            resourceClass: {{$entity}}::class,
            resourceIds: [1, 2],
        );

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall({{$entity}}::class, 1);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_{{$snake_entity}}_response.json', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall({{$entity}}::class, 0);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound();
@endif
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall({{$entity}}::class, 1);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall(
            resourceClass: {{$entity}}::class,
            request: [
                'orderBy' => 'id',
                'orderByDirection' => 'asc',
            ],
        );

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized();
@endif
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall({{$entity}}::class);

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
                'action' => {{$action['className']}}::class,
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
    public function testRun{{$entity}}Actions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall({{$entity}}::class, $action, $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture($state, true);
    }

    public function get{{$entity}}ActionsData(): array
    {
        return [
@foreach($actions as $action)
            [
                'resources' => [1, 2],
                'response_fixture' => 'get_{{$snake_entity}}_actions_{{$action['fixture']}}.json',
            ],
@endforeach
        ];
    }

    /**
     * @dataProvider get{{$entity}}ActionsData
     */
    public function testGet{{$entity}}Actions(array $resources, string $responseFixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall({{$entity}}::class, $resources);

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
                'request' => [
                    '{{$filter['name']}}' => $this->novaSearchParams(['search term']),
                ],
                'response_fixture' => 'filter_{{$snake_entity}}_by_{{$filter['fixture_name']}}.json',
            ],
@endforeach
        ];
    }

    /**
     * @dataProvider get{{$entity}}FiltersData
     */
    public function testFilter{{$entity}}(array $request, string $responseFixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall({{$entity}}::class, $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
