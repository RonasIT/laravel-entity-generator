<?php

namespace RonasIT\Support\Tests\Support;

use org\bovigo\vfs\vfsStream;

trait SeederGeneratorMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'database' => []
        ];

        vfsStream::create($structure);
    }
}