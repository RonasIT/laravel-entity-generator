<?php

namespace RonasIT\EntityGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RonasIT\EntityGenerator\DTO\RelationsDTO;
use RonasIT\EntityGenerator\Enums\FieldTypeEnum;
use RonasIT\EntityGenerator\Events\SuccessCreateMessage;
use RonasIT\EntityGenerator\Events\WarningEvent;
use RonasIT\EntityGenerator\Generators\ControllerGenerator;
use RonasIT\EntityGenerator\Generators\EntityGenerator;
use RonasIT\EntityGenerator\Generators\FactoryGenerator;
use RonasIT\EntityGenerator\Generators\MigrationGenerator;
use RonasIT\EntityGenerator\Generators\ModelGenerator;
use RonasIT\EntityGenerator\Generators\NovaResourceGenerator;
use RonasIT\EntityGenerator\Generators\NovaTestGenerator;
use RonasIT\EntityGenerator\Generators\RepositoryGenerator;
use RonasIT\EntityGenerator\Generators\RequestsGenerator;
use RonasIT\EntityGenerator\Generators\ResourceGenerator;
use RonasIT\EntityGenerator\Generators\SeederGenerator;
use RonasIT\EntityGenerator\Generators\ServiceGenerator;
use RonasIT\EntityGenerator\Generators\TestsGenerator;
use RonasIT\EntityGenerator\Generators\TranslationsGenerator;
use RonasIT\EntityGenerator\Support\Fields\FieldsCollection;
use RonasIT\EntityGenerator\Support\Fields\FieldsParser;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use UnexpectedValueException;

class MakeEntityCommand extends Command
{
    protected FieldsParser $fieldsParser;
    private string $entityName;
    private string $entityNamespace;
    private RelationsDTO $relations;
    private FieldsCollection $fields;

    const CRUD_OPTIONS = [
        'C', 'R', 'U', 'D',
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
        {--only-nova-tests : Set this flag if you want to create only nova resource tests.}

        {--nova-resource-name= : Override the default Nova resource name to generate a Nova test resource.}
        {--methods=CRUD : Set types of methods to create. Affect on routes, requests classes, controller\'s methods and tests methods.} 

        {--i|integer=* : Add integer field to entity.}
        {--f|float=* : Add float field to entity.}
        {--s|string=* : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.}
        {--b|boolean=* : Add boolean field to entity.}
        {--t|timestamp=* : Add timestamp field to entity.}
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

    public function __construct()
    {
        parent::__construct();

        $this->fieldsParser = app(FieldsParser::class);
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->validateInput();
        $this->checkConfigs();
        $this->listenEvents();
        $this->parseFields();
        $this->parseRelations();
        $this->entityName = $this->convertToPascalCase($this->entityName);

        try {
            $this->generate();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function checkConfigs(): void
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

    protected function outputNewConfig(array $packageConfigs, array $projectConfigs): array
    {
        $flattenedPackageConfigs = Arr::dot($packageConfigs);
        $flattenedProjectConfigs = Arr::dot($projectConfigs);

        $newConfig = array_merge($flattenedPackageConfigs, $flattenedProjectConfigs);

        $differences = array_diff_key($newConfig, $flattenedProjectConfigs);

        foreach ($differences as $differenceKey => $differenceValue) {
            $this->comment("Key '{$differenceKey}' was missing in your config, we added it with the value '{$differenceValue}'");
        }

        return array_undot($newConfig);
    }

    protected function customVarExport(array $expression): string
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

    protected function classExists(string $path, string $name): bool
    {
        $paths = config('entity-generator.paths');

        $entitiesPath = $paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function validateInput(): void
    {
        $this->validateEntityName();
        $this->extractEntityNameAndPath();
        $this->validateOnlyApiOption();
        $this->validateCrudOptions();
    }

    protected function generate(): void
    {
        $providedOnlyOptions = $this->getProvidedOnlyOptions();

        $generators = (!empty($providedOnlyOptions))
            ? $this->getOnlyGenerators($providedOnlyOptions)
            : $this->getGeneratorsMap();

        array_walk($generators, fn ($generator) => $this->runGeneration($generator));
    }

    protected function getProvidedOnlyOptions(): array
    {
        $providedOptions = array_filter(
            array: $this->options(),
            callback: fn ($value, $name) => Str::startsWith($name, 'only-') && $value === true,
            mode: ARRAY_FILTER_USE_BOTH,
        );

        return array_keys($providedOptions);
    }

    protected function getOnlyGenerators(array $providedOptions): array
    {
        $generators = Arr::map($providedOptions, fn ($option) => Str::replace('only-', '', $option));

        if (in_array('api', $generators)) {
            array_push($generators, 'resource', 'controller', 'requests', 'factory', 'tests');
        }

        if (in_array('entity', $generators)) {
            array_push($generators, 'migration', 'model', 'repository', 'service', 'factory', 'seeder');
        }

        return array_intersect_key($this->getGeneratorsMap(), array_flip($generators));
    }

    protected function getGeneratorsMap(): array
    {
        return [
            'model' => app(ModelGenerator::class),
            'repository' => app(RepositoryGenerator::class),
            'service' => app(ServiceGenerator::class),
            'resource' => app(ResourceGenerator::class),
            'controller' => app(ControllerGenerator::class),
            'requests' => app(RequestsGenerator::class),
            'migration' => app(MigrationGenerator::class),
            'factory' => app(FactoryGenerator::class),
            'tests' => app(TestsGenerator::class),
            'seeder' => app(SeederGenerator::class),
            'translations' => app(TranslationsGenerator::class),
            'nova-resource' => app(NovaResourceGenerator::class),
            'nova-tests' => app(NovaTestGenerator::class)->setNovaResource($this->option('nova-resource-name')),
        ];
    }

    protected function runGeneration(EntityGenerator $generator): void
    {
        $generator
            ->setModel($this->entityName)
            ->setModelSubFolder($this->entityNamespace)
            ->setFields($this->fields)
            ->setRelations($this->relations)
            ->setCrudOptions($this->getCrudOptions())
            ->generate();
    }

    protected function getCrudOptions(): array
    {
        return str_split($this->option('methods'));
    }

    protected function parseRelations(): void
    {
        $this->relations = new RelationsDTO(
            hasOne: $this->prepareRelations($this->option('has-one')),
            hasMany: $this->prepareRelations($this->option('has-many')),
            belongsTo: $this->prepareRelations($this->option('belongs-to')),
            belongsToMany: $this->prepareRelations($this->option('belongs-to-many')),
        );
    }

    protected function prepareRelations(array $relations): array
    {
        return array_map(function ($relation) {
            $relation = Str::trim($relation, '/');

            return $this->convertToPascalCase($relation);
        }, $relations);
    }

    protected function parseFields(): void
    {
        $rawFields = Arr::only($this->options(), FieldTypeEnum::values());

        $this->fields = $this->fieldsParser->parse($rawFields);
    }

    protected function validateEntityName(): void
    {
        if (!preg_match('/^[A-Za-z0-9\/]+$/', $this->argument('name'))) {
            throw new InvalidArgumentException("Invalid entity name {$this->argument('name')}");
        }
    }

    protected function extractEntityNameAndPath(): void
    {
        list($this->entityName, $entityPath) = extract_last_part($this->argument('name'), '/');

        $this->entityNamespace = Str::trim($entityPath, '/');
    }

    protected function validateCrudOptions(): void
    {
        $crudOptions = $this->getCrudOptions();

        foreach ($crudOptions as $crudOption) {
            if (!in_array($crudOption, MakeEntityCommand::CRUD_OPTIONS)) {
                throw new UnexpectedValueException("Invalid method {$crudOption}.");
            }
        }
    }

    protected function validateOnlyApiOption(): void
    {
        if ($this->option('only-api')) {
            $modelName = Str::studly($this->argument('name'));
            if (!$this->classExists('services', "{$modelName}Service")) {
                throw new ClassNotExistsException('Cannot create API without entity.');
            }
        }
    }

    protected function listenEvents(): void
    {
        Event::listen(
            events: SuccessCreateMessage::class,
            listener: fn (SuccessCreateMessage $event) => $this->info($event->message),
        );

        Event::listen(
            events: WarningEvent::class,
            listener: fn (WarningEvent $event) => $this->warn($event->message),
        );
    }

    protected function convertToPascalCase(string $entityName): string
    {
        $pascalEntityName = Str::studly($entityName);

        if ($entityName !== $pascalEntityName) {
            $this->warn("{$entityName} was converted to {$pascalEntityName}");
        }

        return $pascalEntityName;
    }
}
