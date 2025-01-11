namespace {{$namespace}}\{{$singular_name}};

use Illuminate\Http\Resources\Json\ResourceCollection;

class {{$plural_name}}CollectionResource extends ResourceCollection
{
    public $collects = {{$singular_name}}Resource::class;
}
