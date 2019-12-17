<?php

namespace RonasIT\Support\Generators;

use Illuminate\Filesystem\Filesystem;
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

    /**
     * @param string $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

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

    abstract public function generate();

    protected function classExists($path, $name)
    {
        $entitiesPath = $this->paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function saveClass($path, $name, $content, $additionalEntityFolder = false)
    {
        $entitiesPath = $this->paths[$path];

        if ($additionalEntityFolder) {
            $entitiesPath = $entitiesPath . "/{$additionalEntityFolder}";
        }

        $classPath = base_path("{$entitiesPath}/{$name}.php");
        $tag = "<?php";

        if (!Str::contains($content, $tag)) {
            $content = "{$tag}\n\n{$content}";
        }

        if (!file_exists($entitiesPath)) {
            mkdir_recursively(base_path($entitiesPath));
        }

        return file_put_contents($classPath, $content);
    }

    protected function getStub($stub, $data = [])
    {
        $stubPath = config("entity-generator.stubs.$stub");

        return view($stubPath)->with($data)->render();
    }

    protected function getTableName($entityName)
    {
        $entityName = Str::snake($entityName);

        return Str::plural($entityName);
    }

    protected function getPluralName($entityName)
    {
        return Str::plural($entityName);
    }

    protected function throwFailureException($exceptionClass, $failureMessage, $recommendedMessage)
    {
        throw new $exceptionClass("{$failureMessage} {$recommendedMessage}");
    }
}
