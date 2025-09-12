namespace {{ $namespace }};

use Illuminate\Database\Seeder;
use {{ $factoryNamespace }}\{{ $entity }}Factory;

class {{ $entity }}Seeder extends Seeder
{
    public function run()
    {
@if (empty($relations['belongsTo']))
@if(empty(array_filter($relations)))
        {{ $entity }}Factory::new()->create();
@else
        ${{ strtolower($entity) }} = {{ $entity }}Factory::new()->create();
@endif
@else
@if(empty(array_filter($relations)))
        ${{ strtolower($entity) }} = {{ $entity }}Factory::new()->make([
@else
        {{ $entity }}Factory::new()->make([
@endif
@foreach($relations['belongsTo'] as $relation)
            '{{ strtolower($relation) }}_id' => \{{ $factoryNamespace }}\{{ $relation }}Factory::new()->create()->id,
@endforeach
        ]);
@endif

@foreach($relations['hasOne'] as $relation)
        \{{ $factoryNamespace }}\{{ $relation }}Factory::new()->make([
            '{{ strtolower($entity) }}_id' => ${{ strtolower($entity) }}->id,
        ]);

@endforeach
@foreach($relations['hasMany'] as $relation)
        \{{ $factoryNamespace }}\{{ $relation }}Factory::new()->count(10)->make([
            '{{ strtolower($entity) }}_id' => ${{ strtolower($entity) }}->id,
        ]);

@endforeach
@foreach($relations['belongsToMany'] as $relation)
        $list = \{{ $factoryNamespace }}\{{ $relation }}Factory::new()->count(10)->create()->pluck('id');
        ${{ strtolower($entity) }}->{{ strtolower($relation) }}s()->sync($list);
@endforeach
    }
}