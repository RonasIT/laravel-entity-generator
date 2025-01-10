<?php

namespace RonasIT\Support\Tests\Support\Request;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait RequestMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'app' => [
                'Http' => [
                    'Requests' => [
                        'Posts' => []
                    ],
                ],
                'Services' => [
                    'PostService.php' => '<?php'
                ]
            ],
        ];

        vfsStream::create($structure);
    }
}
