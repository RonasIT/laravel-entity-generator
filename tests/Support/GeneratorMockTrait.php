<?php

namespace RonasIT\Support\Tests\Support;

use Illuminate\Support\Facades\View;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use phpmock\Mock;
use phpmock\MockBuilder;
use RonasIT\Support\Generators\NovaResourceGenerator;
use RonasIT\Support\Generators\NovaTestGenerator;

trait GeneratorMockTrait
{
    public function mockNativeFunction(string $namespace, string $name, $result): Mock
    {
        $builder = new MockBuilder();
        $builder
            ->setNamespace($namespace)
            ->setName($name)
            ->setFunction(function () use ($result) {
                return $result;
            });

        $mock = $builder->build();
        $mock->enable();

        return $mock;
    }

    public function getGeneratorMockForNonExistingNovaResource(string $generatorClass): MockInterface
    {
        $mock = Mockery::mock($generatorClass)->makePartial();

        $mock
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('classExists')
            ->once()
            ->andReturn(false);

        return $mock;
    }

    public function setupConfigurations(): void
    {
        config([
            'entity-generator.stubs.nova_test' => 'entity-generator::nova_test',
            'entity-generator.stubs.dump' => 'entity-generator::dumps.pgsql',
            'entity-generator.paths' => [
                'nova' => 'app/Nova',
                'nova_actions' => 'app/Nova/Actions',
                'tests' => 'tests',
                'models' => 'app/Models'
            ]
        ]);
    }

    public function mockViewsNamespace(): void
    {
        View::addNamespace('entity-generator', getcwd() . '/stubs');
    }

    public function mockClassExistsFunction(bool $result = true): Mock
    {
        return $this->mockNativeFunction('\\RonasIT\\Support\\Generators', 'class_exists', $result);
    }

    public function mockCheckingNonExistentNovaPackageExistence(): Mock
    {
        return $this->mockClassExistsFunction(false);
    }
}
