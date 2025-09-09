<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Tests\Support\Model\ModelMockTrait;
use Symfony\Component\Console\Exception\RuntimeException;

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
            $this->classExistsMethodCall(['models', 'Post', 'Subfolder']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create App\Models\Subfolder model cause it already exists. Remove Post model and run command again.',
        );

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setModelSubFolder('Subfolder')
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
                'integer' => ['priority'],
                'integer-required' => ['media_id'],
                'float' => ['seo_score'],
                'float-required' => ['rating'],
                'string' => ['description'],
                'string-required' => ['title'],
                'boolean' => ['is_reviewed'],
                'boolean-required' => ['is_published'],
                'timestamp' => ['reviewed_at', 'created_at', 'updated_at'],
                'timestamp-required' => ['published_at'],
                'json' => ['meta'],
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

    public function testCreateModelWithoutFields()
    {
        app(ModelGenerator::class)
            ->setModel('Post')
            ->setFields([])
            ->generate();

        $this->assertGeneratedFileEquals('new_model_without_fields.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testSetUnknownFieldType()
    {
        $this->assertExceptionThrew(
            className: RuntimeException::class,
            message: 'The "-l" option does not exist.',
        );

        $this
            ->artisan('make:entity Post -S name -l unknown-type')
            ->assertFailed();
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
            ->artisan('make:entity Post -I media_id -i priority -S title -s description -F rating -f seo_score -B is_published -b is_reviewed -t reviewed_at -t created_at -t updated_at -T published_at -j meta -a Comment -A User --only-model')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_model.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelHasMultipleRelationsWithAnotherModel()
    {
        $this
            ->artisan('make:entity Forum/Post -A User -E User --only-model')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_model_with_many_relations.php', 'app/Models/Forum/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateSubFoldersModel()
    {
        $this
            ->artisan('make:entity Forum/Post -I media_id -i priority -S title -s description -F rating -f seo_score -B is_published -b is_reviewed -t reviewed_at -t created_at -t updated_at -T published_at -j meta -a Comment -A User')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_subfolders_model.php', 'app/Models/Forum/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelWithoutDateFields()
    {
        $this
            ->artisan('make:entity Post -I media_id -i priority -S title -s description -F rating -f seo_score -B is_published -b is_reviewed -j meta --only-model')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_model_without_date_fields.php', 'app/Models/Post.php');

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
