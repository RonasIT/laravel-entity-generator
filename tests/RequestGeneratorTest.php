<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\DTO\RelationsDTO;
use RonasIT\Support\Events\WarningEvent;
use PHPUnit\Framework\Attributes\DataProvider;
use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Generators\RequestsGenerator;
use RonasIT\Support\Exceptions\ResourceAlreadyExistsException;
use RonasIT\Support\Tests\Support\Repository\RepositoryMockTrait;

class RequestGeneratorTest extends TestCase
{
    use RepositoryMockTrait;

    public function testCreateRequests()
    {
        app(RequestsGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                hasMany: ['Comments'],
                belongsTo: ['User'],
            ))
            ->setFields([
                'boolean' => [
                    [
                        'name' => 'is_published',
                        'modifiers' => ['required'],
                    ],
                    [
                        'name' => 'is_draft',
                        'modifiers' => [],
                    ],
                ],
                'integer' => [
                    [
                        'name' => 'user_id',
                        'modifiers' => [],
                    ],
                ],
            ])
            ->setCrudOptions(['C', 'R', 'U', 'D'])
            ->generate();

        $this->assertGeneratedFileEquals('get_request.php', 'app/Http/Requests/Post/GetPostRequest.php');
        $this->assertGeneratedFileEquals('search_request.php', 'app/Http/Requests/Post/SearchPostsRequest.php');
        $this->assertGeneratedFileEquals('delete_request.php', 'app/Http/Requests/Post/DeletePostRequest.php');
        $this->assertGeneratedFileEquals('update_request.php', 'app/Http/Requests/Post/UpdatePostRequest.php');
        $this->assertGeneratedFileEquals('create_request.php', 'app/Http/Requests/Post/CreatePostRequest.php');

        $this->assertEventPushedChain([
            SuccessCreateMessage::class => [
                'Created a new Request: GetPostRequest',
                'Created a new Request: SearchPostsRequest',
                'Created a new Request: DeletePostRequest',
                'Created a new Request: CreatePostRequest',
                'Created a new Request: UpdatePostRequest',
            ],
        ]);
    }

    public function testCreateRequestStubNotExist()
    {
        config(['entity-generator.stubs.request' => 'incorrect_stub']);

        app(RequestsGenerator::class)
            ->setModel('Post')
            ->setCrudOptions(['C'])
            ->generate();

        $this->assertFileDoesNotExist('app/Http/Requests/Post/CreatePostRequest.php');

        $this->assertEventPushed(
            className: WarningEvent::class,
            message: 'Generation of request has been skipped cause the view incorrect_stub from the config entity-generator.stubs.request is not exists. Please check that config has the correct view name value.',
        );
    }

    public static function crudRequestsProvider(): array
    {
        return [
            [
                'type' => 'Create',
                'character' => 'C',
            ],
            [
                'type' => 'Get',
                'character' => 'R',
            ],
            [
                'type' => 'Update',
                'character' => 'U',
            ],
            [
                'type' => 'Delete',
                'character' => 'D',
            ],
        ];
    }

    #[DataProvider('crudRequestsProvider')]
    public function testRequestAlreadyExists(string $type, string $character)
    {
        $this->mockClass(RequestsGenerator::class, [
            $this->classExistsMethodCall(['requests', "Post/{$type}PostRequest"]),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: "Cannot create {$type}PostRequest cause it already exists. Remove app/Http/Requests/Post/{$type}PostRequest.php and run command again.",
        );

        app(RequestsGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                belongsTo: ['User'],
            ))
            ->setCrudOptions([$character])
            ->generate();
    }

    public function testSearchRequestAlreadyExists()
    {
        $this->mockClass(RequestsGenerator::class, [
            $this->classExistsMethodCall(['requests', 'Post/GetPostRequest'], false),
            $this->classExistsMethodCall(['requests', 'Post/SearchPostsRequest']),
        ]);

        $this->assertExceptionThrew(
            className: ResourceAlreadyExistsException::class,
            message: "Cannot create SearchPostsRequest cause it already exists. Remove app/Http/Requests/Post/SearchPostsRequest.php and run command again.",
        );

        app(RequestsGenerator::class)
            ->setModel('Post')
            ->setRelations(new RelationsDTO(
                belongsTo: ['User'],
            ))
            ->setFields([
                'boolean' => [
                    [
                        'name' => 'is_published',
                        'modifiers' => ['required'],
                    ],
                ],
                'integer' => [
                    [
                        'name' => 'user_id',
                        'modifiers' => [],
                    ],
                ],
            ])
            ->setCrudOptions(['R'])
            ->generate();
    }
}
