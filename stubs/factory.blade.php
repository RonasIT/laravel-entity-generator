namespace {{$namespace}};

@if(!empty($fields))use Faker\Generator as Faker;
@endif
use Illuminate\Database\Eloquent\Factories\Factory;
use {{$modelNamespace}}\{{$entity}};

class {{$entity}}Factory extends Factory
{
    protected $model = {{$entity}}::class;

    public function definition(): array
    {
@if(!empty($fields))
        $faker = app(Faker::class);

@endif
        return [
@foreach($fields as $field)
            '{{$field['name']}}' => {!! \RonasIT\Support\Generators\FactoryGenerator::getFactoryFieldsContent($field) !!},
@endforeach
        ];
    }
}
