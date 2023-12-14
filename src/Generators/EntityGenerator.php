<?php

namespace RonasIT\Support\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property Filesystem $fs
 */
abstract class EntityGenerator
{
    const AVAILABLE_FIELDS = [
        'integer', 'integer-required', 'string-required', 'string', 'float-required', 'float',
        'boolean-required', 'boolean', 'timestamp-required', 'timestamp', 'json'
    ];

    protected $paths = [];
    protected $model;
    protected $fields;
    protected $relations;
    protected $crudOptions;

    /**
     * @param array $crudOptions
     * @return $this
     */
    public function setCrudOptions($crudOptions)
    {
        $this->crudOptions = $crudOptions;

        return $this;
    }

    /**
     * @param string $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = Str::studly($model);

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations($relations)
    {
        $this->relations = $relations;

        foreach ($relations['belongsTo'] as $field) {
            $name = Str::snake($field) . '_id';

            $this->fields['integer-required'][] = $name;
        }

        return $this;
    }

    public function __construct()
    {
        $this->paths = config('entity-generator.paths');
    }

    protected function getOrCreateNamespace(string $path): string
    {
        $path = $this->paths[$path];
        $pathParts = explode('/', $path);

        if (Str::endsWith(Arr::last($pathParts), '.php')) {
            array_pop($pathParts);
        }

        $namespace = array_map(function (string $part) {
            return ucfirst($part);
        }, $pathParts);

        $fullPath = base_path($path);

        if (!file_exists($fullPath)) {
            mkdir_recursively($fullPath);
        }

        return implode('\\', $namespace);
    }

    abstract public function generate(): void;

    protected function classExists($path, $name): bool
    {
        $entitiesPath = $this->paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function saveClass($path, $name, $content, $additionalEntityFolder = false): string
    {
        $entitiesPath = base_path($this->paths[$path]);

        if ($additionalEntityFolder) {
            $entitiesPath = $entitiesPath . "/{$additionalEntityFolder}";
        }

        $classPath = "{$entitiesPath}/{$name}.php";
        $tag = "<?php";

        if (!Str::contains($content, $tag)) {
            $content = "{$tag}\n\n{$content}";
        }

        if (!file_exists($entitiesPath)) {
            mkdir_recursively($entitiesPath);
        }

        return file_put_contents($classPath, $content);
    }

    protected function getStub($stub, $data = []): string
    {
        $stubPath = config("entity-generator.stubs.{$stub}");

        $data['options'] = $this->crudOptions;
        dump(scandir(base_path() . '/app'));
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
}
