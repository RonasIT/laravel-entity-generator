<?php

namespace RonasIT\Support\Tests\Support\Model;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ModelMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForExistingModel(): void
    {
        $this->mockClass(ModelGenerator::class, [
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true,
            ],
        ]);
    }

    public function mockGeneratorForMissingRelationModel(): void
    {
        $this->mockClass(ModelGenerator::class, [
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false,
            ],
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Comment'],
                'result' => false,
            ],
        ]);
    }

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
