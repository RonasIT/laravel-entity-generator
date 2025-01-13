<?php

namespace RonasIT\Support\Tests\Support\Translation;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\GeneratorMockTrait;
use RonasIT\Support\Traits\MockTrait;

trait TranslationMockTrait
{
    use GeneratorMockTrait, MockTrait;

    public function mockFilesystem(): void
    {
        $structure = [
            'resources' => [
                'lang' => [
                    'en' => [],
                ],
            ],
        ];

        vfsStream::create($structure);
    }

    public function mockFilesystemForAppend(): void
    {
        $validation = file_get_contents(getcwd() . '/tests/Support/Translation/validation_without_exceptions.php');

        $structure = [
            'resources' => [
                'lang' => [
                    'en' => [
                        'validation.php' => $validation,
                    ],
                ],
            ],
        ];

        vfsStream::create($structure);
    }
}
