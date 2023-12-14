namespace {{$namespace}};

use Illuminate\Database\Seeder;
use {{$modelsNamespace}}\{{$entity}};

class {{$entity}}Seeder extends Seeder
{
    public function run()
    {
@if (empty($relations['belongsTo']))
@if(empty(array_filter($relations)))
        {{$entity}}::factory()->create();
@else
        ${{strtolower($entity)}} = {{$entity}}::factory()->create();
@endif
@else
@if(empty(array_filter($relations)))
        ${{strtolower($entity)}} = {{$entity}}::factory()->make([
@else
        {{$entity}}::factory()->make([
@endif
@foreach($relations['belongsTo'] as $relation)
            '{{strtolower($relation)}}_id' => \{{$modelsNamespace}}\{{$relation}}::factory()->create()->id,
@endforeach
        ]);
@endif

@foreach($relations['hasOne'] as $relation)
        \{{$modelsNamespace}}\{{$relation}}::factory()->make([
            '{{strtolower($entity)}}_id' => ${{strtolower($entity)}}->id,
        ]);

@endforeach
@foreach($relations['hasMany'] as $relation)
        \{{$modelsNamespace}}\{{$relation}}::factory()->count(10)->make([
            '{{strtolower($entity)}}_id' => ${{strtolower($entity)}}->id,
        ]);

@endforeach
@foreach($relations['belongsToMany'] as $relation)
        $list = \{{$modelsNamespace}}\{{$relation}}::factory()->count(10)->create()->pluck('id');
        ${{strtolower($entity)}}->{{strtolower($relation)}}s()->sync($list);
@endforeach
    }
}