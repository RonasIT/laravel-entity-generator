<?php

namespace RonasIT\Support\Tests\Support\Request;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait RequestMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.request' => 'entity-generator::request',
            'entity-generator.paths' => [
                'requests' => 'app/Http/Requests',
                'services' => 'app/Services',
            ]
        ]);
    }

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
