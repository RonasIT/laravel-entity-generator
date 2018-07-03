namespace App\Services;

use App\Repositories\{{$entity}}Repository;
use RonasIT\Support\Services\EntityService;

/**
* @property {{$entity}}Repository $repository
*/
class {{$entity}}Service extends EntityService
{
    public function __construct()
    {
        $this->setRepository({{$entity}}Repository::class);
    }
}