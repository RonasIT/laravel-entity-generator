<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Generators\RequestsGenerator;
use RonasIT\Support\Tests\Support\Request\RequestMockTrait;

class RequestGeneratorTest extends TestCase
{
    use RequestMockTrait;

    public function testCreateRequests()
    {
        $this->mockConfigurations();
        $this->mockViewsNamespace();
        $this->mockFilesystem();

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

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('get_request.php', 'app/Http/Requests/Posts/GetPostRequest.php');
        $this->assertGeneratedFileEquals('search_request.php', 'app/Http/Requests/Posts/SearchPostsRequest.php');
        $this->assertGeneratedFileEquals('delete_request.php', 'app/Http/Requests/Posts/DeletePostRequest.php');
        $this->assertGeneratedFileEquals('update_request.php', 'app/Http/Requests/Posts/UpdatePostRequest.php');
        $this->assertGeneratedFileEquals('create_request.php', 'app/Http/Requests/Posts/CreatePostRequest.php');
    }
}
