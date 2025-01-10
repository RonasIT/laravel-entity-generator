<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Event;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\Model\ModelMockTrait;
use RonasIT\Support\Traits\MockTrait;

class ModelGeneratorTest extends TestCase
{
    use ModelMockTrait, MockTrait;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

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
        $this->mockFilesystem();

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([
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

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelStubNotExist()
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.model' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->setRelations([
                'hasOne' => [],
                'hasMany' => [],
                'belongsTo' => [],
                'belongsToMany' => [],
            ])
            ->generate();

        $this->assertFileDoesNotExist('app/Models/Post.php');
        $this->assertFileDoesNotExist('app/Models/Comment.php');
        $this->assertFileDoesNotExist('app/Models/User.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of model has been skipped cause the view incorrect_stub from the config entity-generator.stubs.model is not exists. Please check that config has the correct view name value.',
        );
    }

    public function testCreateModelWithoutRelationsRelationStubNotExist()
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.relation' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->setRelations([
                'hasOne' => [],
                'hasMany' => [],
                'belongsTo' => [],
                'belongsToMany' => [],
            ])
            ->generate();

        $this->assertGeneratedFileEquals('new_model_without_fields_and_relations.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelWithRelationsRelationStubNotExist()
    {
        $this->mockFilesystem();

        config(['entity-generator.stubs.relation' => 'incorrect_stub']);

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->setRelations([
                'hasOne' => ['Comment'],
                'hasMany' => ['User'],
                'belongsTo' => [],
                'belongsToMany' => [],
            ])
            ->generate();

        $this->assertFileDoesNotExist('new_model.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of model has been skipped cause the view incorrect_stub from the config entity-generator.stubs.relation is not exists. Please check that config has the correct view name value.',
        );
    }
}
