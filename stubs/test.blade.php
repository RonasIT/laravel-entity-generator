@php($shouldUseStatus = version_compare(app()->version(), '7', '<'))
namespace App\Tests;

@if ($withAuth)
use App\Models\User;
@endif
@if($shouldUseStatus)
use Symfony\Component\HttpFoundation\Response;
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

@if (in_array('C', $options))
    public function testCreate()
    {
        $data = $this->getJsonFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

@if (!$withAuth)
        $response = $this->json('post', '/{{$entities}}', $data);
@else
        $response = $this->actingAs($this->user)->json('post', '/{{$entities}}', $data);
@endif

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_CREATED);
@else
        $response->assertCreated()
@endif
        $this->assertEqualsFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_response.json', $response->json());

        $this->assertDatabaseHas('{{$databaseTableName}}', $this->getJsonFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_response.json'));
    }

@if ($withAuth)
    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

        $response = $this->json('post', '/{{$entities}}', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized()
@endif
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
        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/1', $data);
@endif

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent()
@endif

        $this->assertDatabaseHas('{{$databaseTableName}}', $data);
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

@if (!$withAuth)
        $response = $this->json('put', '/{{$entities}}/0', $data);
@else
        $response = $this->actingAs($this->user)->json('put', '/{{$entities}}/0', $data);
@endif

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound()
@endif
    }

@if ($withAuth)
    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_{{\Illuminate\Support\Str::snake($entity)}}_request.json');

        $response = $this->json('put', '/{{$entities}}/1', $data);

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized()
@endif
    }

@endif
@endif
@if (in_array('D', $options))
    public function testDelete()
    {
@if (!$withAuth)
        $response = $this->json('delete', '/{{$entities}}/1');
@else
        $response = $this->actingAs($this->user)->json('delete', '/{{$entities}}/1');
@endif

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NO_CONTENT);
@else
        $response->assertNoContent()
@endif
        $this->assertDatabaseMissing('{{$databaseTableName}}', [
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

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound()
@endif

        $this->assertDatabaseMissing('{{$databaseTableName}}', [
            'id' => 0
        ]);
    }

@if ($withAuth)
    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/{{$entities}}/1');

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
@else
        $response->assertUnauthorized()
@endif
    }

@endif
@endif
@if (in_array('R', $options))
    public function testGet()
    {
@if (!$withAuth)
        $response = $this->json('get', '/{{$entities}}/1');
@else
        $response = $this->actingAs($this->user)->json('get', '/{{$entities}}/1');
@endif

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk()
@endif

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

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_NOT_FOUND);
@else
        $response->assertNotFound()
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

@if($shouldUseStatus)
        $response->assertStatus(Response::HTTP_OK);
@else
        $response->assertOk()
@endif

        // TODO: Need to remove after first successful start
        $this->exportJson($fixture, $response->json());

        $this->assertEqualsFixture($fixture, $response->json());
    }

@endif
}
