<?php

namespace RonasIT\Support\Tests\Support\Translation;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Tests\Support\Shared\GeneratorMockTrait;

trait TranslationMockTrait
{
    use GeneratorMockTrait;

    public function mockConfigurations(): void
    {
        config([
            'entity-generator.stubs.validation' => 'entity-generator::validation',
            'entity-generator.stubs.translation_not_found' => 'entity-generator::translation_not_found',
            'entity-generator.paths' => [
                'translations' => 'resources/lang/en/validation.php',
            ]
        ]);
    }

    public function mockFilesystem(): void
    {
        $structure = [
            'resources' => [
                'lang' => [
                    'en' => []
                ]
            ]
        ];

        vfsStream::create($structure);
    }
}
