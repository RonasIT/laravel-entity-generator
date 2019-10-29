<?php

namespace RonasIT\Support\Generators;

use Illuminate\Support\Arr;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\EntityCreateException;

class SeederGenerator extends EntityGenerator
{
    protected $seedsPath;
    protected $databaseSeederPath;

    public function __construct()
    {
        parent::__construct();

        $this->seedsPath = Arr::get($this->paths, 'seeds', 'database/seeds');
        $this->databaseSeederPath = Arr::get($this->paths, 'database_seeder', 'database/seeds/DatabaseSeeder.php');
    }

    public function generate()
    {
        $this->checkConfigs();

        if (!file_exists($this->databaseSeederPath)) {
            
            if (!is_dir($this->seedsPath)){
                mkdir($this->seedsPath);
            }

            $this->createDatabaseSeeder();
        }

        $this->createEntitySeeder();

        $this->appendSeederToList();
    }

    protected function createDatabaseSeeder()
    {
        $stubPath = config('entity-generator.stubs.database_empty_seeder');

        $content = "<?php \n\n" . view($stubPath)->render();

        file_put_contents($this->databaseSeederPath, $content);

        $createMessage = "Created a new DatabaseSeeder.php on path: {$this->databaseSeederPath}";
        
        event(new SuccessCreateMessage($createMessage));
    }

    protected function createEntitySeeder()
    {
        $stubPath = config('entity-generator.stubs.seeding');

        $content = "<?php \n\n" . view($stubPath)->with([
            'entity' => $this->model,
            'relations' => $this->relations
        ])->render();

        $seederPath = base_path("{$this->seedsPath}/{$this->model}Seeder.php");

        file_put_contents($seederPath, $content);

        $createMessage = "Created a new Seeder on path: {$seederPath}";
        
        event(new SuccessCreateMessage($createMessage));
    }

    protected function appendSeederToList()
    {
        $content = file_get_contents($this->databaseSeederPath);
        
        $insertContent = "\t\$this->call({$this->model}Seeder::class);";

        $fixedContent = preg_replace('/\}\s*\}\s*\z/', "\n\t{$insertContent}\n\t}\n}", $content);
        
        file_put_contents($this->databaseSeederPath, $fixedContent);
    }

    protected function checkConfigs()
    {
        if (empty(config('entity-generator.stubs.seeding'))) {
            throw new EntityCreateException('
                Looks like you have deprecated configs.
                Please follow instructions(https://github.com/RonasIT/laravel-entity-generator/blob/master/ReadMe.md#13)
            ');
        }
    }
}
