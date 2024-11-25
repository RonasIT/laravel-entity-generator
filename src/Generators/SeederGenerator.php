<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\EntityCreateException;

class SeederGenerator extends EntityGenerator
{
    protected $seedsPath;
    protected $databaseSeederPath;

    public function __construct()
    {
        parent::__construct();

        $this->seedsPath = base_path(Arr::get($this->paths, 'seeders', 'database/seeders'));
        $this->databaseSeederPath = base_path(Arr::get($this->paths, 'database_seeder', 'database/seeders/DatabaseSeeder.php'));
    }

    public function generate(): void
    {
        $entitySeeder = (version_compare(app()->version(), '8', '>=')) ? 'seeder' : 'legacy_seeder';

        if (!$this->checkStubExists($entitySeeder) || !$this->checkStubExists('database_empty_seeder')) {
            return;
        }

        if (!file_exists($this->seedsPath)) {
            mkdir($this->seedsPath);
        }

        if (!file_exists($this->databaseSeederPath)) {
            list($basePath, $databaseSeederDir) = extract_last_part($this->databaseSeederPath, '/');

            if (!is_dir($databaseSeederDir)) {
                mkdir($databaseSeederDir);
            }

            $this->createDatabaseSeeder();
        }

        $this->createEntitySeeder($entitySeeder);

        $this->appendSeederToList();
    }

    protected function createDatabaseSeeder(): void
    {
        $content = "<?php \n\n" . $this->getStub('database_empty_seeder', [
            'namespace' => $this->getOrCreateNamespace('seeders')
        ]);

        file_put_contents($this->databaseSeederPath, $content);

        $createMessage = "Created a new DatabaseSeeder.php on path: {$this->databaseSeederPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function createEntitySeeder(string $entitySeeder): void
    {
        $content = "<?php \n\n" . $this->getStub($entitySeeder, [
            'entity' => $this->model,
            'relations' => $this->relations,
            'namespace' => $this->getOrCreateNamespace('seeders'),
            'modelsNamespace' => $this->getOrCreateNamespace('models'),
        ]);

        $seederPath = "{$this->seedsPath}/{$this->model}Seeder.php";

        file_put_contents($seederPath, $content);

        $createMessage = "Created a new Seeder on path: {$seederPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendSeederToList(): void
    {
        $content = file_get_contents($this->databaseSeederPath);

        $insertContent = "    \$this->call({$this->model}Seeder::class);\n    }\n}";

        $fixedContent = preg_replace('/\}\s*\}\s*\z/', $insertContent, $content);

        file_put_contents($this->databaseSeederPath, $fixedContent);
    }
}