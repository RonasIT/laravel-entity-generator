<?php

namespace RonasIT\Support\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\EntityCreateException;
use RonasIT\Support\Generators\ControllerGenerator;
use RonasIT\Support\Generators\EntityGenerator;
use RonasIT\Support\Generators\FactoryGenerator;
use RonasIT\Support\Generators\MigrationGenerator;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Generators\NovaResourceTestGenerator;
use RonasIT\Support\Generators\RepositoryGenerator;
use RonasIT\Support\Generators\RequestsGenerator;
use RonasIT\Support\Generators\ResourceGenerator;
use RonasIT\Support\Generators\ServiceGenerator;
use RonasIT\Support\Generators\TestsGenerator;
use RonasIT\Support\Generators\TranslationsGenerator;
use RonasIT\Support\Generators\SeederGenerator;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use UnexpectedValueException;

/**
 * @property ControllerGenerator $controllerGenerator
 * @property MigrationGenerator $migrationGenerator
 * @property ModelGenerator $modelGenerator
 * @property RepositoryGenerator $repositoryGenerator
 * @property RequestsGenerator $requestsGenerator
 * @property ServiceGenerator $serviceGenerator
 * @property FactoryGenerator $factoryGenerator
 * @property TestsGenerator $testGenerator
 * @property TranslationsGenerator $translationsGenerator
 * @property SeederGenerator $seederGenerator
 * @property ResourceGenerator $resourceGenerator
 * @property NovaResourceGenerator $novaResourceGenerator
 * @property NovaResourceTestGenerator $novaResourceTestGenerator
 * @property EventDispatcher $eventDispatcher
 */
class MakeEntityCommand extends Command
{
    const CRUD_OPTIONS = [
        'C', 'R', 'U', 'D'
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:entity {name : The name of the entity. This name will use as name of models class.}
        
        {--only-api : Set this flag if you want to create resource, controller, route, requests, tests.}
        {--only-entity : Set this flag if you want to create migration, model, repository, service, factory, seeder.}
        {--only-model : Set this flag if you want to create only model. This flag is a higher priority than --only-migration, --only-tests and --only-repository.} 
        {--only-repository : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests and --only-migration.}
        {--only-service : Set this flag if you want to create only service.}
        {--only-resource : Set this flag if you want to create only resource.}
        {--only-controller : Set this flag if you want to create only controller.}
        {--only-requests : Set this flag if you want to create only requests.}
        {--only-migration : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests.}
        {--only-factory : Set this flag if you want to create only factory.}
        {--only-tests : Set this flag if you want to create only tests.}
        {--only-seeder : Set this flag if you want to create only seeder.}
        {--only-nova-resource : Set this flag if you want to create only nova resource.}
        {--only-nova-resource-tests : Set this flag if you want to create only nova resource tests.}

        {--methods=CRUD : Set types of methods to create. Affect on routes, requests classes, controller\'s methods and tests methods.} 

        {--i|integer=* : Add integer field to entity.}
        {--I|integer-required=* : Add required integer field to entity. If you want to specify default value you have to do it manually.}
        {--f|float=* : Add float field to entity.}
        {--F|float-required=* : Add required float field to entity. If you want to specify default value you have to do it manually.}
        {--s|string=* : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.}
        {--S|string-required=* : Add required string field to entity. If you want to specify default value ir size you have to do it manually.}
        {--b|boolean=* : Add boolean field to entity.}
        {--B|boolean-required=* : Add boolean field to entity. If you want to specify default value you have to do it manually.}
        {--t|timestamp=* : Add timestamp field to entity.}
        {--T|timestamp-required=* : Add timestamp field to entity. If you want to specify default value you have to do it manually.}
        {--j|json=* : Add json field to entity.}
        
        {--a|has-one=* : Set hasOne relations between you entity and existed entity.}
        {--A|has-many=* : Set hasMany relations between you entity and existed entity.}
        {--e|belongs-to=* : Set belongsTo relations between you entity and existed entity.}
        {--E|belongs-to-many=* : Set belongsToMany relations between you entity and existed entity.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make entity with Model, Repository, Service, Migration, Controller, Resource and Nova Resource.';

    protected $controllerGenerator;
    protected $migrationGenerator;
    protected $modelGenerator;
    protected $repositoryGenerator;
    protected $requestsGenerator;
    protected $serviceGenerator;
    protected $factoryGenerator;
    protected $testGenerator;
    protected $translationsGenerator;
    protected $seederGenerator;
    protected $resourceGenerator;
    protected $novaResourceGenerator;
    protected $novaResourceTestGenerator;
    protected $eventDispatcher;

    protected $rules = [
        'only' => [
            'only-api' => [ResourceGenerator::class, ControllerGenerator::class, RequestsGenerator::class, TestsGenerator::class],
            'only-entity' => [MigrationGenerator::class, ModelGenerator::class, ServiceGenerator::class, RepositoryGenerator::class, FactoryGenerator::class, SeederGenerator::class],
            'only-model' => [ModelGenerator::class],
            'only-repository' => [RepositoryGenerator::class],
            'only-service' => [ServiceGenerator::class],
            'only-resource' => [ResourceGenerator::class],
            'only-controller' => [ControllerGenerator::class],
            'only-requests' => [RequestsGenerator::class],
            'only-migration' => [MigrationGenerator::class],
            'only-factory' => [FactoryGenerator::class],
            'only-tests' => [FactoryGenerator::class, TestsGenerator::class],
            'only-seeder' => [SeederGenerator::class],
            'only-nova-resource' => [NovaResourceGenerator::class],
            'only-nova-resource-tests' => [NovaResourceTestGenerator::class]
        ]
    ];

    public $generators = [
        ModelGenerator::class, RepositoryGenerator::class, ServiceGenerator::class, RequestsGenerator::class,
        ResourceGenerator::class, ControllerGenerator::class, MigrationGenerator::class, FactoryGenerator::class,
        TestsGenerator::class, TranslationsGenerator::class, SeederGenerator::class, NovaResourceGenerator::class,
        NovaResourceTestGenerator::class
    ];

    public function __construct()
    {
        parent::__construct();

        $this->controllerGenerator = app(ControllerGenerator::class);
        $this->migrationGenerator = app(MigrationGenerator::class);
        $this->modelGenerator = app(ModelGenerator::class);
        $this->repositoryGenerator = app(RepositoryGenerator::class);
        $this->requestsGenerator = app(RequestsGenerator::class);
        $this->serviceGenerator = app(ServiceGenerator::class);
        $this->factoryGenerator = app(FactoryGenerator::class);
        $this->testGenerator = app(TestsGenerator::class);
        $this->translationsGenerator = app(TranslationsGenerator::class);
        $this->seederGenerator = app(SeederGenerator::class);
        $this->resourceGenerator = app(ResourceGenerator::class);
        $this->novaResourceGenerator = app(NovaResourceGenerator::class);
        $this->novaResourceTestGenerator = app(NovaResourceTestGenerator::class);
        $this->eventDispatcher = app(EventDispatcher::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->validateInput();
        $this->checkConfigs();
        $this->eventDispatcher->listen(SuccessCreateMessage::class, $this->getSuccessMessageCallback());

        try {
            $this->generate();
        } catch (EntityCreateException $e) {
            $this->error($e->getMessage());
        }
    }

    protected function checkConfigs()
    {
        $packageConfigPath = __DIR__ . '/../../config/entity-generator.php';
        $packageConfigs = require $packageConfigPath;

        $projectConfigs = config('entity-generator');

        $newConfig = $this->outputNewConfig($packageConfigs, $projectConfigs);

        if ($newConfig !== $projectConfigs) {
            $this->comment('Config has been updated');
            Config::set('entity-generator', $newConfig);
            file_put_contents(config_path('entity-generator.php'), "<?php\n\nreturn" . $this->customVarExport($newConfig) . ';');
        }
    }

    protected function outputNewConfig($packageConfigs, $projectConfigs)
    {
        $flattenedPackageConfigs = Arr::dot($packageConfigs);
        $flattenedProjectConfigs = Arr::dot($projectConfigs);

        $newConfig = array_merge($flattenedPackageConfigs, $flattenedProjectConfigs);

        $translations = 'lang/en/validation.php';
        $translations = (version_compare(app()->version(), '9', '>=')) ? $translations : "resources/{$translations}";

        if ($newConfig['paths.translations'] !== $translations) {
            $newConfig['paths.translations'] = $translations;
        }

        $factories = 'database/factories';
        $factories = (version_compare(app()->version(), '8', '>=')) ? $factories : "{$factories}/ModelFactory.php";

        if ($newConfig['paths.factory'] !== $factories) {
            $newConfig['paths.factory'] = $factories;
        }

        $differences = array_diff_key($newConfig, $flattenedProjectConfigs);

        foreach ($differences as $differenceKey => $differenceValue) {
            $this->comment("Key '{$differenceKey}' was missing in your config, we added it with the value '{$differenceValue}'");
        }

        return array_undot($newConfig);
    }

    protected function customVarExport($expression)
    {
        $defaultExpression = var_export($expression, true);

        $patterns = [
            '/array/' => '',
            '/\(/' => '[',
            '/\)/' => ']',
            '/=> \\n/' => '=>',
            '/=>.+\[/' => '=> [',
            '/^ {8}/m' => str_repeat(' ', 10),
            '/^ {6}/m' => str_repeat(' ', 8),
            '/^ {4}/m' => str_repeat(' ', 6),
            '/^ {2}/m' => str_repeat(' ', 4),
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $defaultExpression);
    }

    protected function classExists($path, $name)
    {
        $paths = config('entity-generator.paths');

        $entitiesPath = $paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function validateInput()
    {
        $this->validateOnlyApiOption();
        $this->validateCrudOptions();
    }

    protected function generate()
    {
        foreach ($this->rules['only'] as $option => $generators) {
            if ($this->option($option)) {
                foreach ($generators as $generator) {
                    $this->runGeneration($generator);
                }

                return;
            }
        }

        foreach ($this->generators as $generator) {
            $this->runGeneration($generator);
        }
    }

    protected function runGeneration($generator)
    {
        app($generator)
            ->setModel($this->argument('name'))
            ->setFields($this->getFields())
            ->setRelations($this->getRelations())
            ->setCrudOptions($this->getCrudOptions())
            ->generate();
    }

    protected function getCrudOptions()
    {
        return str_split($this->option('methods'));
    }

    protected function getRelations()
    {
        return [
            'hasOne' => $this->option('has-one'),
            'hasMany' => $this->option('has-many'),
            'belongsTo' => $this->option('belongs-to'),
            'belongsToMany' => $this->option('belongs-to-many')
        ];
    }

    protected function getSuccessMessageCallback()
    {
        return function (SuccessCreateMessage $event) {
            $this->info($event->message);
        };
    }

    protected function getFields()
    {
        return Arr::only($this->options(), EntityGenerator::AVAILABLE_FIELDS);
    }

    protected function validateCrudOptions()
    {
        $crudOptions = $this->getCrudOptions();

        foreach ($crudOptions as $crudOption) {
            if (!in_array($crudOption, MakeEntityCommand::CRUD_OPTIONS)) {
                throw new UnexpectedValueException("Invalid method {$crudOption}.");
            }
        }
    }

    protected function validateOnlyApiOption()
    {
        if ($this->option('only-api')) {
            $modelName = $this->argument('name');
            if (!$this->classExists('services', "{$modelName}Service")) {
                throw new ClassNotExistsException('Cannot create API without entity.');
            }
        }
    }
}
