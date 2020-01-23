<?php

namespace RonasIT\Support\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;

class ControllerGenerator extends EntityGenerator
{
    public function generate()
    {
        if ($this->classExists('controllers', "{$this->model}Controller")) {
            $this->throwFailureException(
                ClassAlreadyExistsException::class,
                "Cannot create {$this->model}Controller cause {$this->model}Controller already exists.",
                "Remove {$this->model}Controller or run your command with options:'—without-controller'."
            );

        }

        if (!$this->classExists('services', "{$this->model}Service")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Service cause {$this->model}Service does not exists.",
                "Create a {$this->model}Service by himself or run your command with options:'--without-controller --without-migrations --without-requests --without-tests'."
            );
        }

        $controllerContent = $this->getControllerContent($this->model);

        $this->saveClass('controllers', "{$this->model}Controller", $controllerContent);

        $this->createRoutes();

        event(new SuccessCreateMessage("Created a new Controller: {$this->model}Controller"));
    }

    protected function getControllerContent($model)
    {
        return $this->getStub('controller', [
            'entity' => $model,
            'requestsFolder' => $this->getPluralName($model)
        ]);
    }

    protected function createRoutes()
    {
        $routesPath = base_path($this->paths['routes']);

        if (!file_exists($routesPath)) {
            $this->throwFailureException(
                FileNotFoundException::class,
                "Not found file with routes.",
                "Create a routes file on path: '{$routesPath}'."
            );
        }

        $this->addUseController($routesPath);
        $this->addRoutes($routesPath);
    }

    protected function addRoutes($routesPath)
    {
        $routesContent = $this->getStub('routes', [
            'entity' => $this->model,
            'entities' => $this->getTableName($this->model),
        ]);

        $routes = explode("\n", $routesContent);

        foreach ($routes as $route) {
            if (!empty($route)) {
                $createMessage = "Created a new Route: $route";

                event(new SuccessCreateMessage($createMessage));
            }
        }

        return file_put_contents($routesPath, "\n\n{$routesContent}", FILE_APPEND);
    }

    protected function addUseController($routesPath)
    {
        $routesFileContent = file_get_contents($routesPath);

        $stub = $this->getStub('use_routes', [
            'entity' => $this->model
        ]);

        $routesFileContent = preg_replace('/\<\?php[^A-Za-z]*/', "<?php\n\n{$stub}", $routesFileContent);

        file_put_contents($routesPath, $routesFileContent);
    }
}