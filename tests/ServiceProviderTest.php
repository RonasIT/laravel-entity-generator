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
            '/app/vendor/orchestra/testbench-core/laravel/resources/views',
            $finder->getPaths()
        ));
        $this->assertEquals(
            ['/app/src/../config/entity-generator.php' => 'vfs://root/config/entity-generator.php'],
            EntityGeneratorServiceProvider::$publishes['RonasIT\Support\EntityGeneratorServiceProvider']
        );
    }
}