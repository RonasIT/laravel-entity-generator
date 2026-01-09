<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\ModelGenerator;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Tests\Support\Model\ModelMockTrait;
use Symfony\Component\Console\Exception\RuntimeException;
use RonasIT\Support\Exceptions\ResourceNotExistsException;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;

class ModelGeneratorTest extends TestCase
{
    use ModelMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockDefaultFilesystem();
    }

    public function testModelAlreadyExists()
    {
        $this->mockClass(ModelGenerator::class, [
            $this->classExistsMethodCall(['models', 'Post', 'Blog']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: 'Cannot create Post cause it already exists. Remove app/Models/Blog/Post.php and run command again.',
        );

        app(ModelGenerator::class)
            ->setModel('Post')
            ->setModelSubFolder('Blog')
            ->generate();
    }

    public function testRelationModelMissing()
    {
        $this->mockFilesystem([
            'User.php' => file_get_contents(getcwd() . '/tests/Support/Models/WelcomeBonus.php'),
        ]);
        
        $this->assertExceptionThrew(
            className: ResourceNotExistsException::class,
            message: 'Cannot create Post cause Comment does not exist. Create app/Models/Comment.php and run command again.',
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
            ->setFields($this->getJsonFixture('create_model_fields.json'))
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
            ->artisan('make:entity Post -s name -l unknown-type')
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
            ->artisan('make:entity Post -i priority -i media_id:required -f seo_score -f rating:required -s description -s title:required -b is_reviewed -b is_published:required -t reviewed_at -t created_at -t updated_at -t published_at:required -j meta -a Comment -A User --only-model')
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
            ->artisan('make:entity Forum/post -A user -E /user --only-model')
            ->expectsOutput('user was converted to User')
            ->expectsOutput('user was converted to User')
            ->expectsOutput('post was converted to Post')
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
            ->artisan('make:entity Forum/Post -i priority -i media_id:required -f seo_score -f rating:required -s description -s title:required -b is_reviewed -b is_published:required -t reviewed_at -t created_at -t updated_at -t published_at:required -j meta -a Comment -A User')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_subfolders_model.php', 'app/Models/Forum/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateWithSubFoldersRelations()
    {
        $this
            ->artisan('make:entity Post -s title:required -A Forum/Author')
            ->assertSuccessful();

        $this->assertGeneratedFileEquals('new_model_with_subfolers_relations.php', 'app/Models/Post.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Post',
        );
    }

    public function testCreateModelWithoutDateFields()
    {
        $this
            ->artisan('make:entity Post -i priority -i media_id:required -f seo_score -f rating:required -s description -s title:required -b is_reviewed -b is_published:required -j meta --only-model')
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

    public function testAddPropertyAnnotationToRelatedModel()
    {
        app(ModelGenerator::class)
            ->setModel('Category')
            ->setFields([])
            ->setRelations(new RelationsDTO(
                belongsToMany: ['User'],
            ))
            ->generate();

        $this->assertGeneratedFileEquals('related_model_with_property.php', 'app/Models/User.php');

        $this->assertEventPushed(
            className: SuccessCreateMessage::class,
            message: 'Created a new Model: Category',
        );
    }
}
