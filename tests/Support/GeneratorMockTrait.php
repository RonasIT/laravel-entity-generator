<?php

namespace RonasIT\Support\Tests\Support;

use phpmock\Mock;
use phpmock\MockBuilder;

trait GeneratorMockTrait
{
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

    public function mockClassExistsFunction(bool $result = true): Mock
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'class_exists', $result);
    }

    public function mockCheckingNovaPackageExistence(bool $result = false): Mock
    {
        return $this->mockClassExistsFunction($result);
    }

    public function classExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'method' => 'classExists',
            'arguments' => $arguments,
            'result' => $result
        ];
    }
}
