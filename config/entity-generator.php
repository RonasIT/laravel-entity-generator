<?php

return [
    'paths' => [
        'models' => 'app/Models',
        'services' => 'app/Services',
        'requests' => 'app/Http/Requests',
        'controllers' => 'app/Http/Controllers',
        'migrations' => 'database/migrations',
        'seeders' => (version_compare(app()->version(), '8', '>='))
            ? 'database/seeders'
            : 'database/seeds',
        'database_seeder' => (version_compare(app()->version(), '8', '>='))
            ? 'database/seeders/DatabaseSeeder.php'
            : 'database/seeds/DatabaseSeeder.php',
        'repositories' => 'app/Repositories',
        'tests' => 'tests',
        'routes' => 'routes/api.php',
        'factory' => 'database/factories/ModelFactory.php',
        'translations' => 'resources/lang/en/validation.php',
        'resources' => 'app/Http/Resources',
        'nova_actions' => 'app/Nova/Actions',
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
        'legacy_factory' => 'entity-generator::legacy_factory',
        'legacy_empty_factory' => 'entity-generator::legacy_empty_factory',
        'seeder' => 'entity-generator::seeder',
        'legacy_seeder' => 'entity-generator::legacy_seeder',
        'database_empty_seeder' => 'entity-generator::database_empty_seeder',
        'migration' => 'entity-generator::migration',
        'dump' => 'entity-generator::dumps.pgsql',
        'test' => 'entity-generator::test',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'validation' => 'entity-generator::validation',
        'resource' => 'entity-generator::resource',
        'collection_resource' => 'entity-generator::collection_resource',
        'nova_resource' => 'entity-generator::nova_resource',
        'nova_resource_test' => 'entity-generator::nova_resource_test'
    ]
];