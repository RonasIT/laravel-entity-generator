$factory->define({{$modelsNamespace}}\{{$entity}}::class, function (Faker\Generator $faker) {
    return [
@foreach($fields as $field)
        '{{$field['name']}}' => {!! \RonasIT\Support\Generators\FactoryGenerator::getFactoryFieldsContent($field) !!},
@endforeach
    ];
});