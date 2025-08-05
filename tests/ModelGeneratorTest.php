<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\Model\ModelMockTrait;

class ModelGeneratorTest extends TestCase
{
    use ModelMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockFilesystem();
    }

    public function testModelAlreadyExists()
    {
        $this->mockClass(ModelGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post']),
        ]);

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
        $this->mockFileSystemWithoutCommentModel();
        
        $this->assertExceptionThrew(
            className: ClassNotExistsException::class,
            message: "Cannot create Post Model cause relation model Comment does not exist. "
            . "Create the Comment Model by himself or run command 'php artisan make:entity Comment --only-model'.",
        );

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                hasOne: ['Comment']
            ))
            ->generate();
    }

    public function testCreateModel()
    {
        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([
                'integer-required' => ['media_id'],
                'boolean-required' => ['is_published'],
                'timestamp' => ['reviewed_at'],
                'timestamp-required' => ['published_at'],
            ])
            ->setRelations(new RelationsDTO(
                hasOne: ['Comment'],
                hasMany: ['User'],
            ))
            ->generate();

        $this->assertGeneratedFileEquals('new_model.php', 'app/Models/Post.php');
        $this->assertGeneratedFileEquals('comment_relation_model.php', 'app/Models/Comment.php');
        $this->assertGeneratedFileEquals('comment_relation_model.php', 'app/Models/User.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelStubNotExist()
    {
        config(['entity-generator.stubs.model' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->generate();

        $this->assertFileDoesNotExist('app/Models/Post.php');
        $this->assertFileDoesNotExist('app/Models/Comment.php');
        $this->assertFileDoesNotExist('app/Models/User.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of model has been skipped cause the view incorrect_stub from the config entity-generator.stubs.model is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testCreateModelByCommand()
    {
        $this
            ->artisan('make:entity Post -S name -t reviewed_at -T publiched_at --only-model')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('generated_model.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelWithoutRelationsRelationStubNotExist()
    {
        config(['entity-generator.stubs.relation' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO())
            ->setFields([])
            ->generate();

        $this->assertGeneratedFileEquals('new_model_without_fields_and_relations.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelWithRelationsRelationStubNotExist()
    {
        config(['entity-generator.stubs.relation' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->setRelations(new RelationsDTO(
                hasOne: ['Comment'],
                hasMany: ['User'],
            ))
            ->generate();

        $this->assertFileDoesNotExist('new_model.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of model has been skipped cause the view incorrect_stub from the config entity-generator.stubs.relation is not exists. Please check that config has the correct view name value.',
        );
    }
}
