namespace App\Tests;

@if (!empty(array_intersect($options, ['C', 'U', 'D'])))
use RonasIT\Support\Tests\ModelTestState;
use {{$modelsNamespace}}\{{$entity}};
@endif
@if ($withAuth)
use {{$modelsNamespace}}\User;
@endif
@if (in_array('R', $options))
use PHPUnit\Framework\Attributes\DataProvider;
@endif

class {{$entity}}Test extends TestCase
{
@if ($withAuth)
    protected static User $user;

@endif
@if (!empty(array_intersect($options, ['C', 'U', 'D'])))
    protected static ModelTestState ${{\Illuminate\Support\Str::camel($entity)}}State;

@endif
    public function setUp() : void
    {
        parent::setUp();
@if ($withAuth)

        self::$user ??= User::find(1);
@endif
@if (!empty(array_intersect($options, ['C', 'U', 'D'])))

        self::${{\Illuminate\Support\Str::camel($entity)}}State ??= new ModelTestState({{$entity}}::class);
@endif
    }

@if (in_array('C', $options))
    public function testCreate()
    {
        $data = $this->getJsonFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

@if (!$withAuth)
        $response = $this->json('post', '/{{$entities}}', $data);
@else
        $response = $this->actingAs(self::$user)->json('post', '/{{$entities}}', $data);
@endif

        $response->assertCreated();

        $this->assertEqualsFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_response.json', $response->json());

        self::${{\Illuminate\Support\Str::camel($entity)}}State->assertChangesEqualsFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_response.json');
    }

@if ($withAuth)
    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

        $response = $this->json('post', '/{{$entities}}', $data);

        $response->assertUnauthorized();
    }

@endif
@endif
@if (in_array('U', $options))
    public function testUpdate()
    {
        $data = $this->getJsonFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

@if (!$withAuth)
        $response = $this->json('put', '/{{$entities}}/1', $data);
@else
        $response = $this->actingAs(self::$user)->json('put', '/{{$entities}}/1', $data);
@endif

        $response->assertNoContent();

        self::${{\Illuminate\Support\Str::camel($entity)}}State->assertChangesEqualsFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_response.json');
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

@if (!$withAuth)
        $response = $this->json('put', '/{{$entities}}/0', $data);
@else
        $response = $this->actingAs(self::$user)->json('put', '/{{$entities}}/0', $data);
@endif

        $response->assertNotFound();
    }

@if ($withAuth)
    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

        $response = $this->json('put', '/{{$entities}}/1', $data);

        $response->assertUnauthorized();
    }

@endif
@endif
@if (in_array('D', $options))
    public function testDelete()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/1');
@else
        $response = $this->actingAs(self::$user)->json('delete', '/{{$entities}}/1');
@endif

        $response->assertNoContent();

        self::${{\Illuminate\Support\Str::camel($entity)}}State->assertChangesEqualsFixture('delete_{{\Illuminate\Support\Str::snake($entity)}}_response.json');
    }

    public function testDeleteNotExists()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/0');
@else
        $response = $this->actingAs(self::$user)->json('delete', '/{{$entities}}/0');
@endif

        $response->assertNotFound();

        self::${{\Illuminate\Support\Str::camel($entity)}}State->assertNotChanged();
    }

@if ($withAuth)
    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/{{$entities}}/1');

        $response->assertUnauthorized();
    }

@endif
@endif
@if (in_array('R', $options))
    public function testGet()
    {
@if (!$withAuth)
        $response = $this->json('get', '/{{$entities}}/1');
@else
        $response = $this->actingAs(self::$user)->json('get', '/{{$entities}}/1');
@endif

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson('get_{{\Illuminate\Support\Str::snake($entity)}}.json', $response->json());

        $this->assertEqualsFixture('get_{{\Illuminate\Support\Str::snake($entity)}}.json', $response->json());
    }

    public function testGetNotExists()
    {
@if (!$withAuth)
        $response = $this->json('get', '/{{$entities}}/0');
@else
        $response = $this->actingAs(self::$user)->json('get', '/{{$entities}}/0');
@endif

        $response->assertNotFound();
    }

    public static function getSearchFilters()
    {
        return [
            [
                'filter' => ['all' => 1],
                'fixture' => 'search_all.json'
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 2
                ],
                'fixture' => 'search_by_page_per_page.json'
            ],
        ];
    }

    #[DataProvider('getSearchFilters')]
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/{{$entities}}', $filter);

        $response->assertOk();

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }
@endif
}
