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

        $this->assertArrayHasKey('make:entity', Artisan::all());

        $this->assertEquals(
            expected: ['/app/src/../config/entity-generator.php' => 'vfs://root/config/entity-generator.php'],
            actual: EntityGeneratorServiceProvider::$publishes['RonasIT\Support\EntityGeneratorServiceProvider'],
        );
    }
}
