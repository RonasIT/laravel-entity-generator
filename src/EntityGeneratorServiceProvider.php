<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.04.17
 * Time: 19:48
 */
namespace RonasIT\Support;

use Illuminate\Support\ServiceProvider;
use RonasIT\Support\Commands\MakeEntityCommand;

class EntityGeneratorServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->commands([
            MakeEntityCommand::class
        ]);

        $this->publishes([
            __DIR__.'/../config/entity-generator.php' => config_path('entity-generator.php'),
        ], 'config');
    }
}