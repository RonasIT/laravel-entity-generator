namespace {{ $namespace }};

use {{ $modelNamespace }}\{{ $entity }};
use RonasIT\Support\Repositories\BaseRepository;

/**
 * @property {{ $entity }} $model
 */
class {{ $entity }}Repository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel({{ $entity }}::class);
    }
}
