<?php

return [
    'paths' => [
        'base_models' => 'app/Models',
        'models' => 'app/Models',
        'services' => 'app/Services',
        'requests' => 'app/Http/Requests',
        'controllers' => 'app/Http/Controllers',
        'migrations' => 'database/migrations',
        'seeders' => 'database/seeders',
        'database_seeder' => 'database/seeders/DatabaseSeeder.php',
        'repositories' => 'app/Repositories',
        'tests' => 'tests',
        'routes' => 'routes/api.php',
        'factories' => 'database/factories',
        'translations' => 'lang/en/validation.php',
        'resources' => 'app/Http/Resources',
        'nova' => 'app/Nova',
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
        'seeder' => 'entity-generator::seeder',
        'database_empty_seeder' => 'entity-generator::database_empty_seeder',
        'migration' => 'entity-generator::migration',
        'dump' => 'entity-generator::dumps.pgsql',
        'test' => 'entity-generator::test',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'validation' => 'entity-generator::validation',
        'resource' => 'entity-generator::resource',
        'collection_resource' => 'entity-generator::collection_resource',
        'nova_resource' => 'entity-generator::nova_resource',
        'nova_test' => 'entity-generator::nova_test'
    ]
];