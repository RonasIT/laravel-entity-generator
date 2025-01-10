<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Events\SuccessCreateMessage;
use RonasIT\Support\Events\WarningEvent;
use RonasIT\Support\Generators\RequestsGenerator;
use RonasIT\Support\Tests\Support\Request\RequestMockTrait;

class RequestGeneratorTest extends TestCase
{
    use RequestMockTrait;

    public function testCreateRequests()
    {
        app(RequestsGenerator::class)
            ->setModel('Post')
            ->setRelations([
                'belongsTo' => ['User'],
                'hasMany' => ['Comments'],
                'hasOne' => [],
                'belongsToMany' => []
            ])
            ->setFields([
                'boolean-required' => ['is_published'],
                'integer' => ['user_id'],
                'boolean' => ['is_draft'],
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
}
