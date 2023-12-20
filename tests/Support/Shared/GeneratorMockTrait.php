<?php

namespace RonasIT\Support\Tests\Support\Shared;

use Illuminate\Support\Facades\View;
use phpmock\Mock;
use phpmock\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use RonasIT\Support\Traits\MockClassTrait;

trait GeneratorMockTrait
{
    use MockClassTrait;

    protected function mockClassWithReturn($className, $methods = [], $disableConstructor = false): MockObject
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

    public function mockCheckingNonExistentNovaPackageExistence(): Mock
    {
        return $this->mockClassExistsFunction(false);
    }
}
