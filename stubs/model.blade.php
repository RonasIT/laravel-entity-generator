namespace {{$namespace}};

use Illuminate\Database\Eloquent\Model;
use RonasIT\Support\Traits\ModelTrait;

@if(!empty($properties))
/**
@foreach($properties as $fieldNames)
 {{$fieldNames}}
@endforeach
 */
@endif
class {{$entity}} extends Model
{
    use ModelTrait;

    protected $fillable = [
@foreach($fields as $field)
        '{{$field}}',
@endforeach
    ];

    protected $hidden = ['pivot'];
@if(!empty($casts))

    protected $casts = [
@foreach($casts as $fieldName => $cast)
        '{{$fieldName}}' => '{{$cast}}',
@endforeach
    ];
@endif
@foreach($relations as $relation)

    @include(config('entity-generator.stubs.relation'), $relation)

@endforeach
}