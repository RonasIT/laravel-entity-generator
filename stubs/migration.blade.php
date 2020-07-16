use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RonasIT\Support\Traits\MigrationTrait;

class {{$class}}CreateTable extends Migration
{
    use MigrationTrait;

    public function up()
    {
@if(!empty($relations['belongsToMany']) || !empty($relations['belongsTo']) || !empty($relations['hasOne']) || !empty($relations['hasMany']))
        $this->createTable();
@else
        Schema::create('{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}', function (Blueprint $table) {
            $table->increments('id');
@foreach ($table as $row )
            {!!$row!!}
@endforeach
            $table->timestamps();
        });
@endif
@foreach($relations['belongsToMany'] as $relation)

        $this->createBridgeTable('{{$entity}}', '{{$relation}}');
@endforeach
@foreach($relations['belongsTo'] as $relation)

        $this->addForeignKey('{{$entity}}', '{{$relation}}');
@endforeach
@foreach($relations['hasOne'] as $relation)

        $this->addForeignKey('{{$relation}}', '{{$entity}}', true);
@endforeach
@foreach($relations['hasMany'] as $relation)

        $this->addForeignKey('{{$relation}}', '{{$entity}}', true);
@endforeach
    }

    public function down()
    {
@foreach($relations['hasOne'] as $relation)
        $this->dropForeignKey('{{$relation}}', '{{$entity}}', true);

@endforeach
@foreach($relations['hasMany'] as $relation)
        $this->dropForeignKey('{{$relation}}', '{{$entity}}', true);

@endforeach
@foreach($relations['belongsToMany'] as $relation)
        $this->dropBridgeTable('{{$entity}}', '{{$relation}}');

@endforeach
        Schema::dropIfExists('{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}');
    }
@if(!empty($relations['belongsToMany']) || !empty($relations['belongsTo']) || !empty($relations['hasOne']) || !empty($relations['hasMany']))

    public function createTable()
    {
        Schema::create('{{\Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake($entity))}}', function (Blueprint $table) {
            $table->increments('id');
@foreach ($table as $row )
            {!!$row!!}
@endforeach
            $table->timestamps();
        });
    }
@endif
}
