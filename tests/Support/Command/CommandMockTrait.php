<?php

namespace RonasIT\Support\Tests\Support\Command;

use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait CommandMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystemPostModelExists(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = ['Post.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];

        $fileSystemMock->setStructure();
    }

    public function mockFilesystem(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->routes = ['api.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];
        $fileSystemMock->translations = [];

        $fileSystemMock->setStructure();
    }

    public function mockGenerator(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            $this->functionCall(
                name: 'loadNovaActions',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFields',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFilters',
                result: [],
            ),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeIsSubClassOfMethodCall(['App\Nova\PostResource', 'Laravel\\Nova\\Resource']),
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
        );
    }

    public function mockGeneratorSubFolders(): void
    {
        $this->mockClass(NovaTestGenerator::class, [
            $this->functionCall(
                name: 'loadNovaActions',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFields',
                result: [],
            ),
            $this->functionCall(
                name: 'loadNovaFilters',
                result: [],
            ),
        ]);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Forum\Post', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeIsSubClassOfMethodCall(['App\Nova\Forum\PostResource', 'Laravel\\Nova\\Resource']),
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Forum\Post', true]),
        );
    }

    public function nativeIsSubClassOfMethodCall(array $arguments, bool $result = true): array
    {
        return [
            'function' => 'is_subclass_of',
            'arguments' => $arguments,
            'result' => $result,
        ];
    }
}