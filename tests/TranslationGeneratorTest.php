<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\Generators\TranslationsGenerator;
use RonasIT\Support\Tests\Support\Translation\TranslationMockTrait;

class TranslationGeneratorTest extends TestCase
{
    use TranslationMockTrait;

    public function testCreateWithTrait()
    {
        $this->mockViewsNamespace();
        $this->mockConfigurations();
        $this->mockFilesystem();

        app(TranslationsGenerator::class)
            ->setModel('Post')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('translations.php', 'resources/lang/en/validation.php');
    }
}
