namespace {{ $namespace }};

use {{ $modelsNamespace }}\{{ $entity }};
use Illuminate\Pagination\LengthAwarePaginator;
use RonasIT\Support\Traits\EntityControlTrait;
{{--
    Laravel inserts two spaces between @property and type, so we are forced
    to use hack here to preserve one space
--}}
@php
echo <<<PHPDOC
/**
 * @property {$entity} \$model
 */

PHPDOC;
@endphp
class {{ $entity }}Service
{
    use EntityControlTrait;

    public function __construct()
    {
        $this->setModel({{ $entity }}::class);
    }

    public function search(array $filters = []): LengthAwarePaginator
    {
        return $this
            ->searchQuery($filters)
@foreach($fields['simple_search'] as $field)
            ->filterBy('{{ $field }}')
@endforeach
@if(!empty($fields['search_by_query']))
            ->filterByQuery(['{!! implode('\', \'', $fields['search_by_query']) !!}'])
@endif
            ->getSearchResults();
    }
}
