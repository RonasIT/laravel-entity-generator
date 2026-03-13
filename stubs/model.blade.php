namespace {{ $namespace }};

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
@foreach($importRelations as $value)
use {{ $value }};
@endforeach
@if($hasCollectionType)
use Illuminate\Database\Eloquent\Collection;
@endif

/**
 * @property int $id
@foreach($annotationProperties as $key => $value)
 * @property {!! $value !!} ${{ $key }}
@endforeach
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class {{ $entity }} extends Model
{
    use ModelTrait;

    protected $fillable = [
@foreach($fields as $field)
        '{{ $field }}',
@endforeach
    ];

    protected $hidden = ['pivot'];
@if(!empty($casts))

    protected $casts = [
@foreach($casts as $fieldName => $cast)
        '{{ $fieldName }}' => '{{ $cast }}',
@endforeach
    ];
@endif
@foreach($relations as $relation)

    @include(config('entity-generator.stubs.relation'), $relation)

@endforeach
}
