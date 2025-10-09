namespace {{ $namespace }};

@inject('str', 'Illuminate\Support\Str')
@foreach($imports as $import)
use {{ $import }};
@endforeach
use Illuminate\Http\Request;
@foreach($types as $fieldType)
use Laravel\Nova\Fields\{{ $fieldType }};
@endforeach

class {{ $model }}Resource extends Resource
{
    public static $model = {{ $model }}::class;

    //TODO change field for the title if it required
    public static $title = 'name';

    //TODO change query fields if it required
    public static $search = ['id', 'name'];

    public static function label(): string
    {
        return '{{ Str::plural($model) }}';
    }

    public function fields(Request $request): array
    {
        return [
@foreach($fields as $fieldName => $fieldOptions)
@php($name = ctype_upper($fieldName) ? $fieldName : Str::headline($fieldName))
            {{ $fieldOptions['type'] }}::make('{{ $name }}')
@if($fieldOptions['is_required'])
                ->required()
@endif
                ->sortable(),
@endforeach
        ];
    }

    public function cards(Request $request): array
    {
        return [];
    }

    public function filters(Request $request): array
    {
        return [];
    }

    public function lenses(Request $request): array
    {
        return [];
    }

    public function actions(Request $request): array
    {
        return [];
    }
}
