<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 18.10.16
 * Time: 10:30
 */

return [
    'paths' => [
        'models' => 'app/Models',
        'services' => 'app/Services',
        'requests' => 'app/Http/Requests',
        'controllers' => 'app/Http/Controllers',
        'migrations' => 'database/migrations',
        'repositories' => 'app/Repositories',
        'tests' => 'tests',
        'routes' => 'routes/api.php',
        'factory' => 'database/factories/ModelFactory.php'
     ],
    'stubs' => [
        'model' => 'entity-generator::model',
        'relation' => 'entity-generator::relation',
        'repository' => 'entity-generator::repository',
        'service' => 'entity-generator::service',
        'service_with_trait' => 'entity-generator::service_with_trait',
        'controller' => 'entity-generator::controller',
        'request' => 'entity-generator::request',
        'routes' => 'entity-generator::routes',
        'use_routes' => 'entity-generator::use_routes',
        'factory' => 'entity-generator::factory',
        'migration' => 'entity-generator::migration',
        'dump' => 'entity-generator::dumps.pgsql',
        'test' => 'entity-generator::test'
    ]
];