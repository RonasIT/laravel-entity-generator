namespace App\Services;

use RonasIT\Support\Services\EntityService;
use App\Repositories\{{$entity}}Repository;
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

    public function search($filters)
    {
        return $this->repository
            ->searchQuery($filters)
@foreach($fields['simple_search'] as $field)
            ->filterBy('{{$field}}')
@endforeach
@if(!empty($fields['search_by_query']))
            ->filterByQuery(['{!! implode('\', \'', $fields['search_by_query']) !!}'])
@endif
            ->with()
            ->getSearchResults();
    }
}
