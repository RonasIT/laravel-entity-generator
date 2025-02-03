<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Arr;
use org\bovigo\vfs\vfsStream;

class FileSystemMock
{
    public ?array $novaModels = null;
    public ?array $novaActions = null;
    public ?array $models = null;
    public ?array $controllers = null;
    public ?array $services = null;
    public ?array $repositories = null;
    public ?array $testFixtures = null;
    public ?array $testClasses = null;
    public ?array $routes = null;
    public ?array $factories = null;
    public ?array $translations = null;
    public ?array $config = null;

    public function setStructure(): void
    {
        $structure = ['app' => []];

        if (!is_null($this->novaModels)) {
            $structure['app']['Nova'] = [];

            foreach ($this->novaModels as $novaModel => $content) {
                $structure['app']['Nova'][$novaModel] = $content;
            }
        }

        if (!is_null($this->novaActions)) {
            if (!array_key_exists('Nova', $structure['app'])) {
                $structure['app']['Nova'] = [];
            }

            $structure['app']['Nova']['Actions'] = [];

            foreach ($this->novaActions as $novaAction => $content) {
                $structure['app']['Nova']['Actions'][$novaAction] = $content;
            }
        }

        if (!is_null($this->models)) {
            $structure['app']['Models'] = [];

            foreach ($this->models as $model => $content) {
                $structure['app']['Models'][$model] = $content;
            }
        }

        if (!is_null($this->controllers)) {
            $structure['app']['Http']['Controllers'] = [];

            foreach ($this->controllers as $controller => $content) {
                $structure['app']['Http']['Controllers'][$controller] = $content;
            }
        }

        if (!is_null($this->services)) {
            $structure['app']['Services'] = [];

            foreach ($this->services as $service => $content) {
                $structure['app']['Services'][$service] = $content;
            }
        }

        if (!is_null($this->repositories)) {
            $structure['app']['Repositories'] = [];

            foreach ($this->repositories as $repository => $content) {
                $structure['app']['Repositories'][$repository] = $content;
            }
        }

        if (!is_null($this->testClasses)) {
            $structure['tests'] = [];

            foreach ($this->testClasses as $testClass => $content) {
                $structure['app']['tests'][$testClass] = $content;
            }
        }

        if (!is_null($this->testFixtures)) {
            $structure['tests']['fixtures'] = [];

            foreach ($this->testFixtures as $fixture => $content) {
                Arr::set($structure['app']['tests'], $fixture, $content);
            }
        }

        if (!is_null($this->routes)) {
            $structure['routes'] = [];

            foreach ($this->routes as $route => $content) {
                $structure['routes'][$route] = $content;
            }
        }

        if (!is_null($this->factories)) {
            $structure['database']['factories'] = [];

            foreach ($this->factories as $factory => $content) {
                $structure['database']['factories'][$factory] = $content;
            }
        }

        if (!is_null($this->translations)) {
            $structure['lang']['en'] = [];

            foreach ($this->translations as $translation => $content) {
                $structure['lang']['en'][$translation] = $content;
            }
        }

        if (!is_null($this->config)) {
            $structure['config'] = [];

            foreach ($this->config as $config => $content) {
                $structure['config'][$config] = $content;
            }
        }

        vfsStream::create($structure);
    }
}
