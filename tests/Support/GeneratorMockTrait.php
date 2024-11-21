<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Arr;
use Laravel\Nova\NovaServiceProvider;

trait GeneratorMockTrait
{
    public function mockClassExistsFunction(...$rawCallChain): void
    {
        $callChain = array_map(fn ($call) => $this->functionCall(
            name: 'class_exists',
            arguments: [$call['class'], true],
            result: Arr::get($call, 'result', true),
        ), $rawCallChain);

        $this->mockNativeFunction('\RonasIT\Support\Generators', $callChain);
    }

    public function mockNovaServiceProviderExists(bool $result = true): void
    {
        $this->mockClassExistsFunction([
            'class' => NovaServiceProvider::class,
            'result' => $result,
        ]);
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
