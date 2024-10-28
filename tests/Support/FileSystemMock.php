<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Arr;
use org\bovigo\vfs\vfsStream;

class FileSystemMock {
    public $novaModels = null;
    public $novaActions = null;
    public $models = null;
    public $controllers = null;
    public $services = null;
    public $repositories = null;
    public $testFixtures = null;
    public $testClasses = null;
    public $routes = null;

    public function setStructure()
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

        vfsStream::create($structure);
    }
}
