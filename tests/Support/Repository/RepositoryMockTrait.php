<?php

namespace RonasIT\Support\Tests\Support\Repository;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait RepositoryMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Post.php' => '<?php',
                ],
                'Repositories' => [],
            ],
        ];

        vfsStream::create($structure);
    }
}
