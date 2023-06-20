<?php

return [
    'paths' => [
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
        'factory' => 'database/factories/ModelFactory.php',
        'translations' => 'resources/lang/en/validation.php',
        'resources' => 'app/Http/Resources',
        'nova' => 'app/Nova'
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
        'legacy_factory' => 'entity-generator::factory',
        'migration' => 'entity-generator::migration',
        'dump' => 'entity-generator::dumps.pgsql',
        'test' => 'entity-generator::test',
        'legacy_empty_factory' => 'entity-generator::empty_factory',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'validation' => 'entity-generator::validation',
        'legacy_seeder' => 'entity-generator::seeder',
        'database_empty_seeder' => 'entity-generator::database_seed_empty',
        'resource' => 'entity-generator::resource',
        'collection_resource' => 'entity-generator::collection_resource',
        'factory' => 'entity-generator::factory_separate_class',
        'seeder' => 'entity-generator::seeder_for_separate_factory',
        'nova_resource' => 'entity-generator::nova_resource'
    ]
];