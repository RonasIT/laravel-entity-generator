namespace App\Repositories;

use RonasIT\Support\Repositories\BaseRepository;
use App\Models\{{$entity}};

/**
 * @property {{$entity}} $model
*/
class {{$entity}}Repository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel({{$entity}}::class);
    }

    public function search($filters)
    {
        return $this->searchQuery($filters)
@foreach($fields['simple_search'] as $field)
            ->filterBy('{{$field}}')
@endforeach
@if(!empty($fields['search_by_query']))
            ->filterByQuery(['{!! implode('\', \'', $fields['search_by_query']) !!}'])
@endif
            ->orderBy()
            ->getSearchResults();
    }
}