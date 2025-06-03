namespace App\Tests;

use App\Models\{{$entity}};
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Testing\ModelTestState;
use RonasIT\Support\Traits\NovaTestTrait;

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
        $data = $this->getJsonFixture('create_{{$snake_entity}}_request');

        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall({{$entity}}::class, $data);

        $response->assertCreated();

        $this->assertEqualsFixture('create_{{$snake_entity}}_response', $response->json());

        // TODO: Need to remove last argument after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('create_{{$lower_entities}}_state', true);
    }

    public function testCreateNoAuth(): void
    {
        $response = $this->novaCreateResourceAPICall({{$entity}}::class);

        $response->assertUnauthorized();

        self::${{$dromedary_entity}}State->assertNotChanged();
    }

    public function testCreateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaCreateResourceAPICall({{$entity}}::class);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('create_validation_response', $response->json(), true);

        self::${{$dromedary_entity}}State->assertNotChanged();
    }

    public function testUpdate(): void
    {
        $data = $this->getJsonFixture('update_{{$snake_entity}}_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 1, $data);

        $response->assertNoContent();

        // TODO: Need to remove last argument after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('update_{{$lower_entities}}_state', true);
    }

    public function testUpdateNotExists(): void
    {
        $data = $this->getJsonFixture('update_{{$snake_entity}}_request');

        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 0, $data);

        $response->assertNotFound();
    }

    public function testUpdateNoAuth(): void
    {
        $response = $this->novaUpdateResourceAPICall({{$entity}}::class, 1);

        $response->assertUnauthorized();
    }

    public function testUpdateValidationError(): void
    {
        $response = $this->novaActingAs(self::$user)->novaUpdateResourceAPICall({{$entity}}::class, 4);

        $response->assertUnprocessable();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('update_validation_response', $response->json(), true);
    }

    public function testGetUpdatableFields(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetUpdatableFieldsAPICall({{$entity}}::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_updatable_fields_response', $response->json(), true);
    }

    public function testDelete(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall({{$entity}}::class, [1, 2]);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture('delete_{{$lower_entities}}_state', true);
    }

    public function testDeleteNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaDeleteResourceAPICall({{$entity}}::class, [0]);

        $response->assertNotFound();
    }

    public function testDeleteNoAuth(): void
    {
        $response = $this->novaDeleteResourceAPICall({{$entity}}::class, [1, 2]);

        $response->assertUnauthorized();
    }

    public function testGet(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall({{$entity}}::class, 1);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_{{$snake_entity}}_response', $response->json(), true);
    }

    public function testGetNotExists(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetResourceAPICall({{$entity}}::class, 0);

        $response->assertNotFound();
    }

    public function testGetNoAuth(): void
    {
        $response = $this->novaGetResourceAPICall({{$entity}}::class, 1);

        $response->assertUnauthorized();
    }

    public function testSearchUnauthorized(): void
    {
        $response = $this->novaSearchResourceAPICall({{$entity}}::class);

        $response->assertUnauthorized();
    }

    public function testGetFieldsVisibleOnCreate(): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetCreationFieldsAPICall({{$entity}}::class);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture('get_fields_visible_on_create_response', $response->json(), true);
    }

    public static function getRun{{$entity}}ActionsData(): array
    {
        return [
@foreach($actions as $action)
            [
                'action' => {{$action['className']}}::class,
                'request' => [
                    'resources' => '1,2',
                ],
                'state' => 'run_{{$action['fixture']}}_state',
            ],
@endforeach
        ];
    }

    #[DataProvider('getRun{{$entity}}ActionsData')]
    public function testRun{{$entity}}Actions($action, $request, $state): void
    {
        $response = $this->novaActingAs(self::$user)->novaRunActionAPICall({{$entity}}::class, $action, $request);

        $response->assertOk();

        $this->assertEmpty($response->getContent());

        // TODO: Need to remove last argument after first successful start
        self::${{$dromedary_entity}}State->assertChangesEqualsFixture($state, true);
    }

    public static function get{{$entity}}ActionsData(): array
    {
        return [
@foreach($actions as $action)
            [
                'resources' => [1, 2],
                'fixture' => 'get_{{$snake_entity}}_actions_{{$action['fixture']}}',
            ],
@endforeach
        ];
    }

    #[DataProvider('get{{$entity}}ActionsData')]
    public function testGet{{$entity}}Actions(array $resources, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaGetActionsAPICall({{$entity}}::class, $resources);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }

    public static function get{{$entity}}FiltersData(): array
    {
        return [
@foreach($filters as $filter)
            [
                'request' => [
                    '{{$filter['name']}}' => $this->novaSearchParams(['search term']),
                ],
                'fixture' => 'filter_{{$snake_entity}}_by_{{$filter['fixture_name']}}',
            ],
@endforeach
        ];
    }

    #[DataProvider('get{{$entity}}FiltersData')]
    public function testFilter{{$entity}}(array $request, string $fixture): void
    {
        $response = $this->novaActingAs(self::$user)->novaSearchResourceAPICall({{$entity}}::class, $request);

        $response->assertOk();

        // TODO: Need to remove last argument after first successful start
        $this->assertEqualsFixture($fixture, $response->json(), true);
    }
}
