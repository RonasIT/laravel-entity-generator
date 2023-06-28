@if((version_compare(app()->version(), '8', '>=')))
namespace Database\Seeders;
@else
namespace Database\Seeds;
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
