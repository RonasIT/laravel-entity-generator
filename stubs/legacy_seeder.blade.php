namespace Database\Seeds;

use Illuminate\Database\Seeder;

class {{$entity}}Seeder extends Seeder
{
    public function run()
    {
@if (empty($relations['belongsTo']))
@if(empty(array_filter($relations)))
        factory(\{{$modelsNamespace}}\{{$entity}}::class)->create([]);
@else
        ${{strtolower($entity)}} = factory(\{{$modelsNamespace}}\{{$entity}}::class)->create([]);
@endif
@else
@if(empty(array_filter($relations)))
        ${{strtolower($entity)}} = factory(\{{$modelsNamespace}}\{{$entity}}::class)->create([
@else
        factory(\{{$modelsNamespace}}\{{$entity}}::class)->create([
@endif
@foreach($relations['belongsTo'] as $relation)
            '{{strtolower($relation)}}_id' => factory(\{{$modelsNamespace}}\{{$relation}}::class)->create()->id,
@endforeach
        ]);
@endif

@foreach($relations['hasOne'] as $relation)
        factory(\{{$modelsNamespace}}\{{$relation}}::class)->create([
            '{{strtolower($entity)}}_id' => ${{strtolower($entity)}}->id,
        ]);

@endforeach
@foreach($relations['hasMany'] as $relation)
        factory(\{{$modelsNamespace}}\{{$relation}}::class, 10)->create()->each([
            '{{strtolower($entity)}}_id' => ${{strtolower($entity)}}->id,
        ]);

@endforeach
@foreach($relations['belongsToMany'] as $relation)
        $list = factory(\{{$modelsNamespace}}\{{$relation}}::class, 10)->create()->pluck('id');
        ${{strtolower($entity)}}->{{strtolower($relation)}}s()->sync($list);
@endforeach
    }
}