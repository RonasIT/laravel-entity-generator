@if((version_compare(app()->version(), '8', '>=')))
namespace {{$namespace}};
@else
namespace {{$namespace}};
@endif

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    }
}
