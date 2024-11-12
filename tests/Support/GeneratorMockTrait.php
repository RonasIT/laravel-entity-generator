<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\Event;
use Laravel\Nova\NovaServiceProvider;

trait GeneratorMockTrait
{
    public function mockClassExistsFunction(string $className, bool $result = true, bool $autoloadArg = true): void
    {
        $this->mockNativeFunction('\RonasIT\Support\Generators', [
            $this->functionCall(
                name: 'class_exists',
                arguments: [$className, $autoloadArg],
                result: $result,
            ),
        ]);
    }

    public function mockCheckingNovaPackageExistence(string $className, bool $result = false): void
    {
        $this->mockClassExistsFunction($className, $result);
    }

    public function mockNovaServiceProviderExists(bool $result = true): void
    {
        $this->mockClassExistsFunction(NovaServiceProvider::class, $result);
    }

    public function classExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'classExists',
            'arguments' => $arguments,
            'result' => $result
        ];
    }

    public function mockPhpFileContent(): string
    {
        return '<?php';
    }
}
