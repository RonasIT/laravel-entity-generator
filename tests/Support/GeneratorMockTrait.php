<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\DB;
use Laravel\Nova\NovaServiceProvider;
use RonasIT\Support\Traits\MockTrait;

trait GeneratorMockTrait
{
    use MockTrait;

    public function mockNativeGeneratorFunctions(...$functionCalls): void
    {
        $this->mockNativeFunction('\RonasIT\Support\Generators', $functionCalls);
    }

    public function mockNovaServiceProviderExists(bool $result = true): void
    {
        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall([NovaServiceProvider::class, true], $result),
        );
    }

    public function classExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'classExists',
            'arguments' => $arguments,
            'result' => $result
        ];
    }

    public function nativeClassExistsMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'class_exists',
            'arguments' => $arguments,
            'result' => $result,
        ];
    }

    public function mockPhpFileContent(): string
    {
        return '<?php';
    }

    public function mockDBTransactionStartRollback(int $count = 1): void
    {
        DB::shouldReceive('beginTransaction')->times($count);
        DB::shouldReceive('rollBack')->times($count);
    }
}
