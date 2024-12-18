<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\Model\ModelMockTrait;
use RonasIT\Support\Traits\MockTrait;

class ModelGeneratorTest extends TestCase
{
    use ModelMockTrait, MockTrait;

    public function testModelAlreadyExists()
    {
        $this->mockGeneratorForExistingModel();

        $this->assertExceptionThrew(
            className: ClassAlreadyExistsException::class,
            message: 'Cannot create Post Model cause Post Model already exists. Remove Post Model.',
        );

        app(ModelGenerator::class)
            ->setModel('Post')
            ->generate();
    }

    public function testRelationModelMissing()
    {
        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create Post Model cause relation model Comment does not exist. "
            . "Create the Comment Model by himself or run command 'php artisan make:entity Comment --only-model'.",
        );

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'hasOne' => ['Comment'],
                'hasMany' => [],
                'belongsTo' => [],
                'belongsToMany' => [],
            ])
            ->generate();
    }

    public function testCreateModel()
    {
        $this->setupConfigurations();
        $this->mockFilesystem();

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setfields([
                'integer-required' => ['media_id'],
                'boolean-required' => ['is_published'],
            ])
            ->setRelations([
                'hasOne' => ['Comment'],
                'hasMany' => ['User'],
                'belongsTo' => [],
                'belongsToMany' => [],
            ])
            ->generate();

        $this->assertGeneratedFileEquals('new_model.php', 'app/Models/Post.php');
        $this->assertGeneratedFileEquals('comment_relation_model.php', 'app/Models/Comment.php');
        $this->assertGeneratedFileEquals('user_relation_model.php', 'app/Models/User.php');
    }
}
