<?php

namespace RonasIT\Support\Tests;

use RonasIT\Support\EntityGeneratorServiceProvider;
use RonasIT\Support\Exceptions\ClassNotExistsException;
use RonasIT\Support\Tests\Support\Command\CommandMockTrait;
use UnexpectedValueException;

class CommandTest extends TestCase
{
    use CommandMockTrait;

    public function setUp(): void
    {
        parent::setUp();

        $provider = new EntityGeneratorServiceProvider($this->app);
        $provider->boot();
    }

    public function testCallWithInvalidCrudOption()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectErrorMessage('Invalid method T');

        $this->artisan('make:entity Post --methods=T');
    }

    public function testCallWithMissingModelService()
    {
        $this->mockConfigurations();;

        $this->expectException(ClassNotExistsException::class);
        $this->expectErrorMessage('Cannot create API without entity.');

        $this->artisan('make:entity Post --only-api');
    }

    public function testCallCommand()
    {
        $this->mockConfigurations();
        $this->mockFilesystem();

        $this->artisan('make:entity Post --methods=CRUD')
            ->assertSuccessful();
    }

    public function testMakeOnly()
    {
        $this->mockConfigurations();
        $this->mockFilesystem();

        $this->artisan('make:entity Post --methods=CRUD --only-repository')
            ->assertSuccessful();
    }
}
