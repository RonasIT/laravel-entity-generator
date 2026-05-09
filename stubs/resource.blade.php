namespace {{ $namespace }}\{{ $entity }};

use Illuminate\Http\Request;
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
    public function toArray(Request $request): array
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
