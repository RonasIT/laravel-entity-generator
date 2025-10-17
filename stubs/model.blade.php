namespace {{ $namespace }};

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;
@foreach($importRelations as $value)
use {{ $value }};
@endforeach
@if($hasCarbonField)
use Carbon\Carbon;
@endif
@if($hasCollectionType)
use Illuminate\Database\Eloquent\Collection;
@endif

@if(!empty($annotationProperties))
/**
@foreach($annotationProperties as $key => $value)
 * @property {!! $value !!} ${{ $key }}
@endforeach
 */
@else
//TODO: add @property annotation for each model's field
/**
 */
@endif
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
