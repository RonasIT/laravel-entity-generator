<?php

namespace RonasIT\Support\Tests\Support;

use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Generators\NovaTestGenerator;

trait NovaResourceMockTrait
{
    use GeneratorMockTrait;

    public function mockNovaResourceGenerator(): void
    {
        $mock = $this
            ->getMockBuilder(NovaResourceGenerator::class)
            ->onlyMethods(['getModelFields', 'getMockModel', 'loadNovaActions', 'loadNovaFields', 'loadNovaFilters'])
            ->getMock();

        $mock
            ->method('getModelFields')
            ->willReturn(['title', 'name']);

        $mock
            ->method('getMockModel')
            ->willReturn(['title' => 'some title', 'name' => 'some name']);

        $mock
            ->method('loadNovaActions')
            ->willReturn([
                new PublishPostAction,
                new UnPublishPostAction,
                new UnPublishPostAction,
            ]);

        $mock
            ->method('loadNovaFields')
            ->willReturn([
                new TextField,
                new DateField,
            ]);

        $mock
            ->method('loadNovaFilters')
            ->willReturn([
                new CreatedAtFilter,
            ]);

        $this->app->instance(NovaTestGenerator::class, $mock);
    }

    public function getResourceGeneratorMockForNonExistingNovaResource(): MockInterface
    {
        return $this->getGeneratorMockForNonExistingNovaResource(NovaResourceGenerator::class);
    }

    public function getResourceGeneratorMockForExistingNovaResource(): MockInterface
    {
        $mock = Mockery::mock(NovaResourceGenerator::class)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->with('models', 'Post')
            ->andReturn(true);

        $mock
            ->shouldReceive('classExists')
            ->once()
            ->with('nova', 'PostResource')
            ->andReturn(true);

        return $mock;
    }

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.nova_resource' => 'entity-generator::nova_resource',
            'entity-generator.stubs.dump' => 'entity-generator::dumps.pgsql',
            'entity-generator.paths' => [
                'nova' => 'app/Nova',
                'models' => 'app/Models'
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Nova' => [],
                'Models' => [
                    'Post.php' => '<?php'
                ]
            ],
        ];

        vfsStream::create($structure);
    }
}