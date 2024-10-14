<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use org\bovigo\vfs\vfsStream;

trait SeederGeneratorMockTrait
{
    public function mockFilesystem(): void
    {
        $structure = [
            'database' => []
        ];

        vfsStream::create($structure);
    }

    public function mockViewsNamespace(): void
    {
        View::addNamespace('entity-generator', getcwd() . '/stubs');
    }
}