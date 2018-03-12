<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 18.10.16
 * Time: 10:22
 */

namespace RonasIT\Support\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * @property Filesystem $fs
 */
abstract class EntityGenerator
{
    protected $paths = [];
    protected $model;
    protected $fields;
    protected $relations;

    /**
     * @param string $model
     * @return $this
     */
    public function setModel($model) {
        $this->model = $model;

        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields($fields) {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations($relations) {
        $this->relations = $relations;

        foreach ($relations['belongsTo'] as $field) {
            $name = snake_case($field).'_id';

            $this->fields['integer-required'][] = $name;
        }

        return $this;
    }

    public function __construct()
    {
        $this->paths = config('entity-generator.paths');
    }

    abstract public function generate();

    protected function classExists($path, $name) {
        $entitiesPath = $this->paths[$path];

        $classPath = base_path("{$entitiesPath}/{$name}.php");

        return file_exists($classPath);
    }

    protected function saveClass($path, $name, $content) {
        $entitiesPath = $this->paths[$path];
        $classPath = base_path("{$entitiesPath}/{$name}.php");
        $tag = "<?php\n\n";

        if(!str_contains($content, $tag)) {
            $content = "{$tag}{$content}";
        }

        if (!file_exists($entitiesPath)) {
            mkdir_recursively(base_path($entitiesPath));
        }

        return file_put_contents($classPath, $content);
    }

    protected function getStub($stub, $data = []) {
        $stubPath = config("entity-generator.stubs.$stub");

        return view($stubPath)->with($data)->render();
    }

    protected function getTableName($entityName) {
        $entityName = snake_case($entityName);

        return Str::plural($entityName);
    }

    protected function getTableClassName($entityName) {
        return Str::plural($entityName);
    }

    protected function throwFailureException($exceptionClass, $failureMessage, $recommendedMessage) {
        throw new $exceptionClass("{$failureMessage} {$recommendedMessage}");
    }
}