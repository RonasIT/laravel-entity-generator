<?php

namespace RonasIT\Support\Generators;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use RonasIT\Support\DTO\FieldsSchemaDTO;
use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\IncorrectClassPathException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;
use Throwable;

/**
 * @property Filesystem $fs
 */
abstract class EntityGenerator
{
    const AVAILABLE_FIELDS = [
        'integer', 'string', 'float', 'boolean', 'timestamp', 'json',
    ];

    const LOWER_CASE_DIRECTORIES_MAP = [
        'migrations' => 'database/migrations',
        'factories' => 'database/factories',
        'seeders' => 'database/seeders',
        'database_seeder' => 'database/seeders',
        'tests' => 'tests',
        'routes' => 'routes',
        'translations' => 'lang/en',
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
        $this->model = $model;

        return $this;
    }

    public function setModelSubFolder(string $folder): self
    {
        $this->modelSubFolder = $folder;

        return $this;
    }

    public function setFields(FieldsSchemaDTO $fields): self
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

            $this->fields->integer[] = $this->convertToField($name, ['required']);
        }

        return $this;
    }

    public function __construct()
    {
        $this->paths = config('entity-generator.paths');

        $this->checkConfigHasCorrectPaths();
    }

    protected function generateNamespace(string $path, ?string $additionalSubFolder = null): string
    {
        $pathParts = $this->getNamespacePathParts($path, $additionalSubFolder);

        $namespace = array_map(fn (string $part) => ucfirst($part), $pathParts);

        return implode('\\', $namespace);
    }

    protected function createNamespace(string $configPath, ?string $subFolder = null): void
    {
        $path = $this->getPath($this->paths[$configPath], $subFolder);

        $fullPath = base_path($path);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }
    }

    protected function getNamespacePathParts(string $path, ?string $additionalSubFolder = null): array
    {
        $pathParts = explode('/', $this->getPath($path, $additionalSubFolder));

        if (Str::endsWith(Arr::last($pathParts), '.php')) {
            array_pop($pathParts);
        }

        return $pathParts;
    }

    protected function getPath(string $path, ?string $subFolder = null): string
    {
        return when($subFolder, fn () => Str::finish($path, '/') . $subFolder, $path);
    }

    protected function isFolderHasCorrectCase(string $folder, string $configPath): bool
    {
        $directory = Arr::get(self::LOWER_CASE_DIRECTORIES_MAP, $configPath);

        $firstFolderChar = substr($folder, 0, 1);

        return $folder === 'app' || (ucfirst($firstFolderChar) === $firstFolderChar) || Str::contains($directory, $folder);
    }

    abstract public function generate(): void;

    protected function classExists(string $path, string $name, ?string $subFolder = null): bool
    {
        $relativePath = $this->getClassPath($path, $name, $subFolder);

        $absolutePath = base_path($relativePath);

        return file_exists($absolutePath);
    }

    protected function getClassPath(string $path, string $name, ?string $subFolder = null): string
    {
        $path = $this->getPath($this->paths[$path], $subFolder);

        $extension = (str_contains($name, '.')) ? '' : '.php';

        return "{$path}/{$name}{$extension}";
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
        $tag = '<?php';

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
            throw new ResourceNotExistsException($creatableClass, $model);
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

        $modelNamespace = $this->generateNamespace($this->paths['models'], $subfolder);

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
        return ucwords(Str::replace('/', '\\', $name), '\\');
    }

    protected function checkConfigHasCorrectPaths(): void
    {
        foreach ($this->paths as $configPath => $path) {
            $pathParts = $this->getNamespacePathParts($path);

            foreach ($pathParts as $part) {
                if (!$this->isFolderHasCorrectCase($part, $configPath)) {
                    throw new IncorrectClassPathException("Incorrect path to {$configPath}, {$part} folder must start with a capital letter, please specify the path according to the PSR.");
                }
            }
        }
    }

    protected function checkResourceExists(string $path, string $resourceName, ?string $subFolder = null): void
    {
        if ($this->classExists($path, $resourceName, $subFolder)) {
            $filePath = $this->getClassPath($path, $resourceName, $subFolder);

            throw new ResourceAlreadyExistsException($filePath);
        }
    }

    protected function checkResourceNotExists(string $path, string $creatableResource, string $requiredResource, ?string $subFolder = null): void
    {
        if (!$this->classExists($path, $requiredResource, $subFolder)) {
            $filePath = $this->getClassPath($path, $requiredResource, $subFolder);

            throw new ResourceNotExistsException($creatableResource, $filePath);
        }
    }

    protected function getRelationName(string $relation, string $type): string
    {
        $relationName = Str::snake($relation);

        if ($this->isPluralRelation($type)) {
            $relationName = Str::plural($relationName);
        }

        return $relationName;
    }

    protected function isPluralRelation(string $relation): bool
    {
        return in_array($relation, ['hasMany', 'belongsToMany']);
    }

    protected function convertToField(string $name, array $modifiers): array
    {
        return [
            'name' => $name,
            'modifiers' => $modifiers,
        ];
    }
}
