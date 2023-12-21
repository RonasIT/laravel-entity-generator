<?php

namespace RonasIT\Support\Tests\Support\Repository;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\RepositoryGenerator;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait RepositoryMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForMissingModel(): void
    {
        $this->mockClass(RepositoryGenerator::class, [
            [
                'method' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ],
        ]);
    }

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.repository' => 'entity-generator::repository',
            'entity-generator.paths' => [
                'repositories' => 'app/Repositories',
                'models' => 'app/Models',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => '<?php'
                ],
                'Repositories' => []
            ],
        ];

        vfsStream::create($structure);
    }
}
