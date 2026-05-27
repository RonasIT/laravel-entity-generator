namespace {{ $namespace }}\{{ $singular_name }};

use Illuminate\Http\Resources\Json\ResourceCollection;

final class {{ $plural_name }}CollectionResource extends ResourceCollection
{
    public $collects = {{ $singular_name }}Resource::class;
}
