<?php

namespace RonasIT\EntityGenerator\Tests\Support\Command;

use org\bovigo\vfs\vfsStream;
use RonasIT\EntityGenerator\Generators\NovaTestGenerator;
use RonasIT\EntityGenerator\Tests\Support\FileSystemMock;
use RonasIT\EntityGenerator\Tests\Support\GeneratorMockTrait;

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

    public function mockFilesystemForOnlyApi(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->services = ['PostService.php' => $this->mockPhpFileContent()];
        $fileSystemMock->config = ['entity-generator.php' => ''];

        $fileSystemMock->setStructure();

        $structure = [];
        $structure['RonasIT']['EntityGenerator']['Tests']['Support']['Command']['Models']['Post.php'] = $this->mockPhpFileContent();

        vfsStream::create($structure);

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall(['RonasIT\EntityGenerator\Tests\Support\Command\Models\Post', true]),
        );
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
            $this->nativeClassExistsMethodCall(['RonasIT\EntityGenerator\Tests\Support\Command\Models\Post', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeIsSubClassOfMethodCall(['App\Nova\PostResource', 'Laravel\\Nova\\Resource']),
            $this->nativeClassExistsMethodCall(['RonasIT\EntityGenerator\Tests\Support\Command\Models\Post', true]),
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
            $this->nativeClassExistsMethodCall(['RonasIT\EntityGenerator\Tests\Support\Command\Models\Forum\Post', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeIsSubClassOfMethodCall(['App\Nova\Forum\PostResource', 'Laravel\\Nova\\Resource']),
            $this->nativeClassExistsMethodCall(['RonasIT\EntityGenerator\Tests\Support\Command\Models\Forum\Post', true]),
        );
    }

    public function nativeIsSubClassOfMethodCall(array $arguments, bool $result = true): array
    {
        return $this->functionCall('is_subclass_of', $arguments, $result);
    }
}
