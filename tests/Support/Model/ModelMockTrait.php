<?php

namespace RonasIT\Support\Tests\Support\Model;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ModelMockTrait
{
    use GeneratorMockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Comment.php' => file_get_contents(getcwd() . '/tests/Support/Model/RelationModelMock.php'),
                    'User.php' => file_get_contents(getcwd() . '/tests/Support/Model/RelationModelMock.php'),
                ],
            ],
        ];

        vfsStream::create($structure);
    }
}
