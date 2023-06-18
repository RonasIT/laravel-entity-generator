namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;

class {{$entity}}ResourceCollection extends ResourceCollection
{
    public $collects = {{$entity}}Resource::class;

    public function paginationInformation($request, $paginated, $default)
    {
        return Arr::except($paginated, 'data');
    }
}