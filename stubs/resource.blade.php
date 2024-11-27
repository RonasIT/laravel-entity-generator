namespace {{$namespace}};

use Illuminate\Http\Resources\Json\JsonResource;
use {{$model_namespace}}\{{$entity}};

/**
  * @property {{$entity}} $resource
 */
class {{$entity}}Resource extends JsonResource
{
    //TODO implement custom serialization logic or remove method redefining
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}