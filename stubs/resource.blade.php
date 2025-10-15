namespace {{ $namespace }}\{{ $entity }};

use RonasIT\Support\Http\BaseResource;
use {{ $model_namespace }}\{{ $entity }};

/**
 * @property {{ $entity }} $resource
 */
class {{ $entity }}Resource extends BaseResource
{
@if (empty($fields))
    //TODO implement custom serialization logic or remove method redefining
@endif
    public function toArray($request): array
    {
    @if (!empty($fields))
    return [
    @foreach($fields as $field)
        '{{ $field }}' => $this->resource->{{ $field }},
    @endforeach
    ];
    @else
    return parent::toArray($request);
    @endif
}
}
