namespace App\Services;

use App\Repositories\{{$entity}}Repository;
use RonasIT\Support\Services\EntityService;
{{--
    Laravel inserts two spaces between @property and type, so we are forced
    to use hack here to preserve one space
--}}
@php
echo <<<PHPDOC
/**
 * @mixin {$entity}Repository
 * @property {$entity}Repository \$repository
 */

PHPDOC;
@endphp
class {{$entity}}Service extends EntityService
{
    public function __construct()
    {
        $this->setRepository({{$entity}}Repository::class);
    }
}
