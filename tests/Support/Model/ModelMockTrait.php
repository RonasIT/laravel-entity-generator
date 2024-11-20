<?php

namespace RonasIT\Support\Tests\Support\Model;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;

trait ModelMockTrait
{
    use GeneratorMockTrait;

    public function mockGeneratorForExistingModel()
    {
        $this->mockClass(ModelGenerator::class, [
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => true
            ]
        ]);
    }

    public function mockGeneratorForMissingRelationModel()
    {
        $this->mockClass(ModelGenerator::class, [
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Post'],
                'result' => false
            ],
            [
                'function' => 'classExists',
                'arguments' => ['models', 'Comment'],
                'result' => false
            ]
        ]);
    }

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.model' => 'entity-generator::model',
            'entity-generator.stubs.relation' => 'entity-generator::relation',
            'entity-generator.paths' => [
                'models' => 'app/Models',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Models' => [
                    'Comment.php' => file_get_contents(getcwd() . '/tests/Support/Model/RelationModelMock.php'),
                    'User.php' => file_get_contents(getcwd() . '/tests/Support/Model/RelationModelMock.php')
                ]
            ],
        ];

        vfsStream::create($structure);
    }
}
