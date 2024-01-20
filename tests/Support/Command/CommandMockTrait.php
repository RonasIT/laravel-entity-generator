<?php

namespace RonasIT\Support\Tests\Support\Command;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait CommandMockTrait
{
    use GeneratorMockTrait;

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.service' => 'entity-generator::service',
            'entity-generator.stubs.service_with_trait' => 'entity-generator::service_with_trait',
            'entity-generator.paths' => [
                'repositories' => 'app/Repositories',
                'services' => 'app/Services',
                'models' => 'app/Models',
                'translations' => 'lang/en/validation.php'
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
            'config' => [
                'entity-generator.php' => ''
            ]
        ];

        vfsStream::create($structure);
    }
}