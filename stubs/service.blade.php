namespace App\Services;

use App\Repositories\{{$entity}}Repository;
use Illuminate\Support\Arr;
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

    public function search($filters)
    {
        return $this
            ->with(Arr::get($filters, 'with', []))
            ->withCount(Arr::get($filters, 'with_count', []))
            ->searchQuery($filters)
@foreach($fields['simple_search'] as $field)
            ->filterBy('{{$field}}')
@endforeach
@if(!empty($fields['search_by_query']))
            ->filterByQuery(['{!! implode('\', \'', $fields['search_by_query']) !!}'])
@endif
            ->getSearchResults();
    }
}
