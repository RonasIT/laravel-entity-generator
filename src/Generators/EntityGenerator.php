<?php

namespace RonasIT\Support\Generators;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\IncorrectClassPathException;
use Throwable;
use ReflectionMethod;
use ReflectionClass;

/**
 * @property Filesystem $fs
 */
abstract class EntityGenerator
{
    const AVAILABLE_FIELDS = [
        'integer', 'integer-required', 'string-required', 'string', 'float-required', 'float',
        'boolean-required', 'boolean', 'timestamp-required', 'timestamp', 'json'
    ];

    const LOVER_CASE_DIRECTORIES_MAP = [
        'migrations' => 'database/migrations',
        'factories' => 'database/factories',
        'seeders' => 'database/seeders',
        'database_seeder' => 'database/seeders',
        'tests' => 'tests',
        'routes' => 'routes',
    ];

    protected $paths = [];
    protected $model;
    protected $modelSubFolder = '';
    protected $fields;
    protected $relations = [];
    protected $crudOptions;


    public function setCrudOptions(array $crudOptions): self
    {
        $this->crudOptions = $crudOptions;

        return $this;
    }

    public function setModel(string $model): self
    {
        $this->model = Str::studly($model);

        return $this;
    }

    public function setModelSubFolder(string $folder): self
    {
        $this->modelSubFolder = $folder;

        return $this;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function setRelations(RelationsDTO $relations): self
    {
        $this->relations = $relations;

        foreach ($relations->belongsTo as $field) {
            $relatedModel = Str::afterLast($field, '/');

            $name = Str::snake($relatedModel) . '_id';

            $this->fields['integer-required'][] = $name;
        }

        return $this;
    }

    public function __construct()
    {
        $this->paths = config('entity-generator.paths');
    }

    protected function getNamespace(string $configPath, ?string $subFolder = null): string
    {
        $pathParts = $this->getNamespacePathParts($configPath, $subFolder);

        $namespace = array_map(fn (string $part) => ucfirst($part), $pathParts);

        return implode('\\', $namespace);
    }

    protected function createNamespace(string $configPath, ?string $subFolder = null): void
    {
        $path = $this->getPath($configPath, $subFolder);

        $fullPath = base_path($path);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
    }

    protected function getNamespacePathParts(string $configPath, ?string $subFolder = null): array
    {
        $pathParts = explode('/', $this->getPath($configPath, $subFolder));

        if (Str::endsWith(Arr::last($pathParts), '.php')) {
            array_pop($pathParts);
        }

        foreach ($pathParts as $part) {
            if (!$this->isFolderHasCorrectCase($part, $configPath)) {
                throw new IncorrectClassPathException("Incorrect path to {$configPath}, {$part} folder must start with a capital letter, please specify the path according to the PSR.");
            }
        }

        return $pathParts;
    }

    protected function getPath(string $configPath, ?string $subFolder = null): string
    {
        return when($subFolder, fn () => Str::finish($this->paths[$configPath], '/') . $subFolder, $this->paths[$configPath]);
    }

    protected function isFolderHasCorrectCase(string $folder, string $configPath): bool
    {
        $directory = Arr::get(self::LOVER_CASE_DIRECTORIES_MAP, $configPath);

        $firstFolderChar = substr($folder, 0, 1);

        return $folder === 'app' || (ucfirst($firstFolderChar) === $firstFolderChar) || Str::contains($directory, $folder);
    }

    abstract public function generate(): void;

    protected function classExists(string $path, string $name, ?string $subFolder = null): bool
    {
        $classPath = $this->getClassPath($path, $name, $subFolder);

        return file_exists($classPath);
    }

    protected function getClassPath(string $path, string $name, ?string $subFolder = null): string
    {
        $path = $this->getPath($path, $subFolder);

        return base_path("{$path}/{$name}.php");
    }

    protected function saveClass($path, $name, $content, ?string $entityFolder = null): string
    {
        $entitiesPath = base_path($this->paths[$path]);

        if (Str::endsWith($entitiesPath, '.php')) {
            list(, $entitiesPath) = extract_last_part($entitiesPath, '/');
        }

        if (!empty($entityFolder)) {
            $entitiesPath = "{$entitiesPath}/{$entityFolder}";
        }

        $classPath = "{$entitiesPath}/{$name}.php";
        $tag = "<?php";

        if (!Str::contains($content, $tag)) {
            $content = "{$tag}\n\n{$content}";
        }

        if (!file_exists($entitiesPath)) {
            mkdir($entitiesPath, 0777, true);
        }

        return file_put_contents($classPath, $content);
    }

    protected function getStub($stub, $data = []): string
    {
        $stubPath = config("entity-generator.stubs.{$stub}");

        $data['options'] = $this->crudOptions;

        return view($stubPath)->with($data)->render();
    }

    protected function getTableName($entityName, $delimiter = '_'): string
    {
        $entityName = Str::snake($entityName, $delimiter);

        return $this->getPluralName($entityName);
    }

    protected function getPluralName($entityName): string
    {
        return Str::plural($entityName);
    }

    protected function throwFailureException($exceptionClass, $failureMessage, $recommendedMessage): void
    {
        throw new $exceptionClass("{$failureMessage} {$recommendedMessage}");
    }

    protected function getRelatedModels(string $model, string $creatableClass): array
    {
        $modelClass = $this->getModelClass($model);

        if (!class_exists($modelClass)) {
            $this->throwFailureException(
                exceptionClass: ClassNotExistsException::class,
                failureMessage: "Cannot create {$creatableClass} cause {$model} Model does not exists.",
                recommendedMessage: "Create a {$model} Model by himself or run command 'php artisan make:entity {$model} --only-model'.",
            );
        }

        $instance = new $modelClass();

        $publicMethods = (new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC);

        $methods = array_filter($publicMethods, fn ($method) => $method->class === $modelClass && !$method->getParameters());

        $relatedModels = [];

        DB::beginTransaction();

        foreach ($methods as $method) {
            try {
                $result = call_user_func([$instance, $method->getName()]);

                if (!$result instanceof BelongsTo) {
                    continue;
                }
            } catch (Throwable) {
                continue;
            }

            $relatedModels[] = $this->generateRelativePath(get_class($result->getRelated()), $this->paths['models']);
        }

        DB::rollBack();

        return $relatedModels;
    }

    protected function generateRelativePath(string $namespace, string $basePath): string
    {
        return Str::after(
            subject: $this->namespaceToPath($namespace),
            search: $this->namespaceToPath($basePath) . '/',
        );
    }

    protected function namespaceToPath(string $namespace): string
    {
        return str_replace('\\', '/', $namespace);
    }

    protected function getModelClass(string $model): string
    {
        $subfolder = when($model === $this->model, $this->modelSubFolder);

        $modelNamespace = $this->getNamespace('models', $subfolder);

        return "{$modelNamespace}\\{$model}";
    }

    protected function isStubExists(string $stubName, ?string $generationType = null): bool
    {
        $config = "entity-generator.stubs.{$stubName}";

        $stubPath = config($config);

        if (!view()->exists($stubPath)) {
            $generationType ??= Str::replace('_', ' ', $stubName);

            $message = "Generation of {$generationType} has been skipped cause the view {$stubPath} from the config {$config} is not exists. Please check that config has the correct view name value.";

            event(new WarningEvent($message));

            return false;
        }

        return true;
    }

    protected function prepareRelations(): array
    {
        $result = [];

        foreach ($this->relations as $relationType => $relations) {
            $result[$relationType] = array_map(fn ($relation) => class_basename($relation), $relations);
        }

        return $result;
    }

    protected function pathToNamespace(string $name): string
    {
        return Str::replace('/', '\\', $name);
    }
}
