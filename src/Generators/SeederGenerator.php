<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningMessage;
use RonasIT\Support\Exceptions\EntityCreateException;

class SeederGenerator extends EntityGenerator
{
    protected $seedsPath;
    protected $databaseSeederPath;

    public function __construct()
    {
        parent::__construct();

        $this->seedsPath = Arr::get($this->paths, 'seeders', 'database/seeders');
        $this->databaseSeederPath = Arr::get($this->paths, 'database_seeder', 'database/seeders/DatabaseSeeder.php');
    }

    public function generate(): void
    {
        if (!file_exists($this->databaseSeederPath)) {
            list($basePath, $databaseSeederDir) = extract_last_part($this->databaseSeederPath, '/');

            if (!is_dir($databaseSeederDir)) {
                mkdir($databaseSeederDir);
            }

            $this->createDatabaseSeeder();
        }

        if (!is_dir($this->seedsPath)) {
            mkdir($this->seedsPath);
        }

        $this->createEntitySeeder();

        $this->appendSeederToList();
    }

    protected function createDatabaseSeeder(): void
    {
        $stubPath = config('entity-generator.stubs.database_empty_seeder');

        //@TODO: remove after implementing https://github.com/RonasIT/laravel-entity-generator/issues/93
        if ($stubPath === 'entity-generator::database_seed_empty') {
            $stubPath = 'entity-generator::database_empty_seeder';

            $message = "You are using the deprecated value for 'entity-generator.stubs.database_empty_seeder' config. Please use 'entity-generator::database_empty_seeder'.";

            event(new WarningMessage($message));
        }

        $content = "<?php \n\n" . view($stubPath, [
            'namespace' => $this->getOrCreateNamespace('seeders')
        ])->render();

        file_put_contents($this->databaseSeederPath, $content);

        $createMessage = "Created a new DatabaseSeeder.php on path: {$this->databaseSeederPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function createEntitySeeder(): void
    {
        $seeder = (version_compare(app()->version(), '8', '>=')) ? 'seeder' : 'legacy_seeder';

        $stubPath = config("entity-generator.stubs.{$seeder}");

        $content = "<?php \n\n" . view($stubPath)->with([
            'entity' => $this->model,
            'relations' => $this->relations,
            'namespace' => $this->getOrCreateNamespace('seeders'),
            'modelsNamespace' => $this->getOrCreateNamespace('models')
        ])->render();

        $seederPath = base_path("{$this->seedsPath}/{$this->model}Seeder.php");

        file_put_contents($seederPath, $content);

        $createMessage = "Created a new Seeder on path: {$seederPath}";

        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendSeederToList(): void
    {
        $content = file_get_contents($this->databaseSeederPath);

        $insertContent = "\n        \$this->call({$this->model}Seeder::class);\n    }\n}";

        $fixedContent = preg_replace('/\}\s*\}\s*\z/', $insertContent, $content);

        file_put_contents($this->databaseSeederPath, $fixedContent);
    }
}