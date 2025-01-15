<?php

namespace RonasIT\Support\Tests;

use Illuminate\Support\Facades\Artisan;
use RonasIT\Support\EntityGeneratorServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function testBoot()
    {
        $provider = new EntityGeneratorServiceProvider($this->app);
        $provider->boot();

        $finder = view()->getFinder();

        $this->assertArrayHasKey('make:entity', Artisan::all());

        $this->assertTrue(in_array(
            needle: '/app/vendor/orchestra/testbench-core/laravel/resources/views',
            haystack: $finder->getPaths(),
        ));

        $this->assertEquals(
            expected: ['/app/src/../config/entity-generator.php' => 'vfs://root/config/entity-generator.php'],
            actual: EntityGeneratorServiceProvider::$publishes['RonasIT\Support\EntityGeneratorServiceProvider'],
        );
    }
}
