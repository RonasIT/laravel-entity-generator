namespace Database\Factories;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class {{$entity}}Factory extends Factory
{
    public function definition(): array
    {
        $faker = app(Faker::class);

        return [
@foreach($fields as $field)
            '{{$field['name']}}' => {!! \RonasIT\Support\Generators\FactoryGenerator::getFactoryFieldsContent($field) !!},
@endforeach
        ];
    }
}