use Illuminate\Database\Seeder;
use App\Models;

class {{$entity}}Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
@if (empty($relations['belongsTo']))
@if(empty(array_filter($relations)))
        factory(App\Models\{{$entity}}::class)->create([]);
@else
        ${{strtolower($entity)}} = factory(App\Models\{{$entity}}::class)->create([]);
@endif
@else
@if(empty(array_filter($relations)))
        ${{strtolower($entity)}} = factory(App\Models\{{$entity}}::class)->create([
@else
        factory(App\Models\{{$entity}}::class)->create([
@endif
@foreach($relations['belongsTo'] as $relation)
            '{{strtolower($relation)}}_id' => factory(App\Models\{{$relation}}::class)->create()->id,
@endforeach
        ]);
@endif

@foreach($relations['hasOne'] as $relation)
        factory(App\Models\{{$relation}}::class)->create([
            '{{strtolower($entity)}}_id' => ${{$entity}}->id,
        ]);

@endforeach
@foreach($relations['hasMany'] as $relation)
        factory(App\Models\{{$relation}}::class, 10)->create()->each([
            '{{strtolower($entity)}}_id' => ${{$entity}}->id,
        ]);

@endforeach
@foreach($relations['belongsToMany'] as $relation)
        $list = factory(App\Models\{{$relation}}::class, 10)->create()->pluck('id');
        ${{strtolower($entity)}}->{{strtolower($relation)}}s->sync($list);
@endforeach
    }
}
