namespace {{$namespace}};

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class {{$entity}}Factory extends Factory
{
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