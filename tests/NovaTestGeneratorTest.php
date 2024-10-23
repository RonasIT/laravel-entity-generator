<?php

namespace RonasIT\Support\Tests;

use org\bovigo\vfs\vfsStream;
use RonasIT\Support\Exceptions\ClassAlreadyExistsException;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Generators\NovaTestGenerator;
use RonasIT\Support\Tests\Support\NovaTestMockTrait;

class NovaTestGeneratorTest extends TestCase
{
    use NovaTestMockTrait;

    public function testCreateNovaTestsResourceNotExists()
    {
        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassNotExistsException::class);
        $this->expectExceptionMessage("Cannot create NovaWelcomeBonusTest cause WelcomeBonus Nova resource does not exist. Create WelcomeBonus Nova resource.");

        $generatorMock = $this->getGeneratorMockForNonExistingNovaResource();

        try {
            $generatorMock
                ->setModel('WelcomeBonus')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreateNovaTestAlreadyExists()
    {
        $this->setupConfigurations();

        $mock = $this->mockClassExistsFunction();

        $this->expectException(ClassAlreadyExistsException::class);
        $this->expectExceptionMessage("Cannot create NovaWelcomeBonusTest cause it's already exist. Remove NovaWelcomeBonusTest.");

        $generatorMock = $this->getGeneratorMockForExistingNovaResourceTest();

        try {
            $generatorMock
                ->setModel('WelcomeBonus')
                ->generate();
        } finally {
            $mock->disable();
        }
    }

    public function testCreateWithActions()
    {
        $functionMock = $this->mockClassExistsFunction();

        $this->mockFilesystem();
        $this->setupConfigurations();
        $this->mockViewsNamespace();
        $this->mockNovaResourceTestGenerator();

        app(NovaTestGenerator::class)
            ->setModel('WelcomeBonus')
            ->generate();

        $this->rollbackToDefaultBasePath();

        $this->assertGeneratedFileEquals('created_resource_test.php', 'tests/NovaWelcomeBonusTest.php', true);
        $this->assertGeneratedFileEquals('dump.sql', 'tests/fixtures/NovaWelcomeBonusTest/nova_welcome_bonus_dump.sql');
        $this->assertGeneratedFileEquals('create_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_request.json');
        $this->assertGeneratedFileEquals('create_welcome_bonus_response.json', 'tests/fixtures/NovaWelcomeBonusTest/create_welcome_bonus_response.json');
        $this->assertGeneratedFileEquals('update_welcome_bonus_request.json', 'tests/fixtures/NovaWelcomeBonusTest/update_welcome_bonus_request.json');

        $functionMock->disable();
    }
}
