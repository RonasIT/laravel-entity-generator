<?php

namespace RonasIT\Support\Tests\Support;

use phpmock\Mock;

trait GeneratorMockTrait
{
    public function mockClassExistsFunction(bool $result = true): void
    {
        $this->mockNativeFunction('\RonasIT\Support\Generators', [
            $this->functionCall(
                name: 'class_exists',
                result: $result,
            ),
        ]);
    }

    public function mockCheckingNovaPackageExistence(bool $result = false): void
    {
        $this->mockClassExistsFunction($result);
    }

    public function classExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'classExists',
            'arguments' => $arguments,
            'result' => $result
        ];
    }
}
