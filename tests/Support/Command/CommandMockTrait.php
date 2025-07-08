<?php

namespace RonasIT\Support\Tests\Support\Command;

use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\FileSystemMock;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use Mockery;

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

    public function mockFilesystemWithPostModelAndResource(): void
    {
        $fileSystemMock = new FileSystemMock();

        $fileSystemMock->models = ['Post.php' => $this->mockPhpFileContent()];
        $fileSystemMock->novaModels = ['PostResource.php' => $this->mockPhpFileContent()];
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
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
        );
    }
    public function mockGeneratorOnlyNovaTests(): void
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
            $this->classExistsMethodCall(['nova', 'NovaPostResourceTest'], false),
            $this->classExistsMethodCall(['models', 'Post']),
            $this->classExistsMethodCall(['models', 'User'], false),
            $this->classExistsMethodCall(['factories', 'PostFactory']),
            $this->classExistsMethodCall(['factories', 'PostFactory']),
        ]);

        $mock = Mockery::mock('alias:Illuminate\Support\Facades\DB');
        $mock
            ->shouldReceive('beginTransaction', 'rollBack')
            ->once();

        $this->mockNativeGeneratorFunctions(
            $this->nativeClassExistsMethodCall(['Laravel\Nova\NovaServiceProvider', true]),
            $this->nativeClassExistsMethodCall(['RonasIT\Support\Tests\Support\Command\Models\Post', true]),
        );
    }
}