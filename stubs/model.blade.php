namespace App\Models;

use RonasIT\Support\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;

class {{$entity}} extends Model
{
    use ModelTrait;

    protected $fillable = [
@foreach($fields as $field)
        '{{$field}}',
@endforeach
    ];

    protected $hidden = ['pivot'];
@foreach($relations as $relation)

    @include(config('entity-generator.stubs.relation'), $relation)

@endforeach
}