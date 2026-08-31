namespace {{ $namespace }}\{{ $entity }};

use Illuminate\Http\Request;
use RonasIT\Support\Http\BaseResource;
use {{ $model_namespace }}\{{ $entity }};
@foreach($relation_imports as $import)
use {{ $import }};
@endforeach

/**
 * @property {{ $entity }} $resource
 */
final class {{ $entity }}Resource extends BaseResource
{
@if (empty($fields))
    //TODO implement custom serialization logic or remove method redefining
@endif
    public function toArray(Request $request): array
    {
@if (!empty($fields))
        return [
@foreach($fields as $field)
            '{{ $field }}' => $this->resource->{{ $field }},
@endforeach
@foreach($relations as $relation)
            '{{ $relation['name'] }}' => {{ $relation['resource'] }}::make($this->whenLoaded('{{ $relation['name'] }}')),
@endforeach
        ];
@else
        return parent::toArray($request);
@endif
    }
}
