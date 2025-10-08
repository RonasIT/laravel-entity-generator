namespace {{ $namespace }}\{{ $entity }};

use RonasIT\Support\Http\BaseResource;
use {{ $model_namespace }}\{{ $entity }};

/**
 * @property {{ $entity }} $resource
 */
class {{ $entity }}Resource extends BaseResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
