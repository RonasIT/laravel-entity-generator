namespace App\Nova;

@inject('str', 'Illuminate\Support\Str')
use App\Models\{{$model}};
use Illuminate\Http\Request;
@foreach($types as $fieldType)
use Laravel\Nova\Fields\{{$fieldType}};
@endforeach

class {{$model}}Resource extends Resource
{
    public static $model = {{$model}}::class;

    //TODO change field for the title if it required
    public static $title = 'name';

    //TODO change query fields if it required
    public static $search = ['id', 'name'];

    public static function label(): string
    {
        return '{{Str::plural($model)}}';
    }

    public function fields(Request $request): array
    {
        return [
        @foreach($fields as $fieldName => $fieldOptions)
            @php
                $headline = Str::headline($fieldName);
                $result = "{$fieldOptions['type']}::make('{$headline}')";

                echo ($fieldOptions['is_required'])
                ? "\n\t\t\t{$result}
                ->sortable()
                ->required(),\n"
                : "\n\t\t\t{$result}
                ->sortable(),\n"
            @endphp
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