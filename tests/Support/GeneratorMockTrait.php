<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use phpmock\Mock;
use phpmock\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;

trait GeneratorMockTrait
{
    protected function mockByClassBuilder($className, $methods = [], $disableConstructor = false): MockObject
    {
        $builder = $this->getMockBuilder($className);

        if ($methods) {
            $builder->onlyMethods($methods);
        }

        if ($disableConstructor) {
            $builder->disableOriginalConstructor();
        }

        return $builder->getMock();
    }

    public function mockNativeFunction(string $namespace, string $name, $result): Mock
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName($name)
            ->setFunction(function () use ($result) {
                return $result;
            });

        $mock = $builder->build();
        $mock->enable();

        return $mock;
    }

    public function mockViewsNamespace(): void
    {
        View::addNamespace('entity-generator', getcwd() . '/stubs');
    }

    public function mockClassExistsFunction(bool $result = true): Mock
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'class_exists', $result);
    }

    public function mockCheckingNovaPackageExistence(bool $result = false): Mock
    {
        return $this->mockClassExistsFunction($result);
    }

    public function classExistsMethodCall(?string $path, ?string $className, bool $result = true): array
    {
        return [
            'method' => 'classExists',
            'arguments' => $path || $className ? [$path, $className] : [],
            'result' => $result
        ];
    }
}
