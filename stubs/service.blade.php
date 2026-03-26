namespace {{ $namespace }};

use {{ $repositoriesNamespace }}\{{ $entity }}Repository;
use RonasIT\Support\Services\EntityService;
use Illuminate\Pagination\LengthAwarePaginator;
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
class {{ $entity }}Service extends EntityService
{
    public function __construct()
    {
        $this->setRepository({{ $entity }}Repository::class);
    }

    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this
            ->searchQuery($filters)
@if(!empty($fields['search_by_query']))
            ->filterByQuery(['{!! implode('\', \'', $fields['search_by_query']) !!}'])
@endif
            ->getSearchResults();
    }
}
