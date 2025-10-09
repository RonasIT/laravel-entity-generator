<?php

namespace RonasIT\Support\Generators;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;

class ControllerGenerator extends EntityGenerator
{
    public function generate(): void
    {
        if ($this->classExists('controllers', "{$this->model}Controller")) {
            $path = $this->getClassPath('controllers', "{$this->model}Controller");

            throw new ResourceAlreadyExistsException($path);
        }

        if (!$this->classExists('services', "{$this->model}Service")) {
            $this->throwFailureException(
                ClassNotExistsException::class,
                "Cannot create {$this->model}Controller cause {$this->model}Service does not exists.",
                "Create a {$this->model}Service by himself.",
            );
        }

        if (!$this->isStubExists('controller')) {
            return;
        }

        $this->createNamespace('controllers');

        $controllerContent = $this->getControllerContent($this->model);

        $this->saveClass('controllers', "{$this->model}Controller", $controllerContent);

        $this->createRoutes();

        event(new SuccessCreateMessage("Created a new Controller: {$this->model}Controller"));
    }

    protected function getControllerContent($model): string
    {
        return $this->getStub('controller', [
            'entity' => $model,
            'requestsFolder' => $model,
            'namespace' => $this->getNamespace('controllers'),
            'requestsNamespace' => $this->getNamespace('requests'),
            'resourcesNamespace' => $this->getNamespace('resources'),
            'servicesNamespace' => $this->getNamespace('services'),
        ]);
    }

    protected function createRoutes(): void
    {
        $routesPath = base_path($this->paths['routes']);

        if (!file_exists($routesPath)) {
            $this->throwFailureException(
                FileNotFoundException::class,
                "Not found file with routes.",
                "Create a routes file on path: '{$routesPath}'.",
            );
        }

        if ($this->isStubExists('routes') && $this->isStubExists('use_routes')) {
            $this->addUseController($routesPath);
            $this->addRoutes($routesPath);
        }
    }

    protected function addRoutes($routesPath): string
    {
        $routesContent = $this->getStub('routes', [
            'entity' => $this->model,
            'entities' => $this->getTableName($this->model, '-'),
        ]);

        $routes = explode("\n", $routesContent);
        $routes = array_slice($routes, 1, array_key_last($routes) - 1);

        foreach ($routes as $route) {
            if (!empty($route)) {
                $route = trim($route);

                $createMessage = "Created a new Route: $route";

                event(new SuccessCreateMessage($createMessage));
            }
        }

        return file_put_contents($routesPath, "\n\n{$routesContent}", FILE_APPEND);
    }

    protected function addUseController(string $routesPath): void
    {
        $routesFileContent = file_get_contents($routesPath);

        $stub = $this->getStub('use_routes', [
            'namespace' => $this->getNamespace('controllers'),
            'entity' => $this->model
        ]);

        $routesFileContent = preg_replace('/\<\?php[^A-Za-z]*/', "<?php\n\n{$stub}", $routesFileContent);

        file_put_contents($routesPath, $routesFileContent);
    }
}
