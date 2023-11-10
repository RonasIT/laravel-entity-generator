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

    protected $user;

    public function setUp() : void
    {
        parent::setUp();

        $this->user = 1;

        $this->skipDocumentationCollecting();
    }

    public function testCreate(): void
    {
        $modelState = new ModelTestState({{$entity}}::class);
        $data = $this->getJsonFixture('create_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('post', '/nova-api/{{$url_path}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated();
@endif
        $this->assertEqualsFixture('create_{{$lower_entity}}_response.json', $response->json());

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $modelTestState->getFixturePath('create_{{$lower_entities}}_state.json'),
            $modelTestState->getChanges(),
            true
        );
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
    }

    public function testCreateValidationError(): void
    {
        $modelState = new ModelTestState({{$entity}}::class);
        $response = $this->actingViaSession($this->user['id'])->json('post', '/nova-api/{{$url_path}}', []);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif

        $this->assertEquals($modelState->getExpectedEmptyState(), $modelState->getChanges());
    }

    public function testUpdate(): void
    {
        $modelState = new ModelTestState({{$entity}}::class);
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/{{$url_path}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $modelTestState->getFixturePath('update_{{$lower_entities}}_state.json'),
            $modelTestState->getChanges(),
            true
        );
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_{{$lower_entity}}_request.json');

        $response = $this->actingViaSession($this->user)->json('put', '/nova-api/{{$url_path}}/0', $data);

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
        $response = $this->actingViaSession($this->user['id'])->json('put', '/nova-api/{{$url_path}}/4', []);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
@else
        $response->assertUnprocessable();
@endif
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->actingViaSession($this->user['id'])->json('get', '/nova-api/{{$url_path}}/1/update-fields');

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
        $modelState = new ModelTestState({{$entity}}::class);
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/{{$url_path}}', [
            'resources' => [1, 2]
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $modelTestState->getFixturePath('delete_{{$lower_entities}}_state.json'),
            $modelTestState->getChanges(),
            true
        );
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->actingViaSession($this->user)->json('delete', '/nova-api/{{$url_path}}', [
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
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/1');

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
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/0');

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

    public function testSearch(): void
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}', [
            'orderBy' => 'id',
            'orderByDirection' => 'asc'
        ]);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('search_{{$lower_entities}}_response.json', $response->json(), true);
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
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/creation-fields');

        $response->assertStatus(Response::HTTP_OK);

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response.json', $response->json(), true);
    }

    public function getRun{{$entity}}ActionData(): array
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
     * @dataProvider getRun{{$entity}}ActionData
     */
    public function testRun{{$entity}}Action($action, $request, ${{$lower_entities}}StateFixture): void
    {
        $modelState = new ModelTestState({{$entity}}::class);
        $response = $this->actingViaSession($this->user)->json('post', "/nova-api/{{$url_path}}/action?action={$action}", $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        $this->assertEmpty($response->getContent());
        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture(
            $modelTestState->getFixturePath(${{$lower_entities}}StateFixture),
            $modelTestState->getChanges(),
            true
        );
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
    public function testGet{{$entity}}Actions($request, $responseFixture): void
    {
        $response = $this->actingViaSession($this->user)->json('get', '/nova-api/{{$url_path}}/actions', $request);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk();
@endif

        // TODO: Need to remove after first successful start
        $this->assertEqualsFixture($responseFixture, $response->json(), true);
    }
}
