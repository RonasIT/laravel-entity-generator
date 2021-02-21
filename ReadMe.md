# Laravel-Entity-Generator

Laravel-Entity-Generator - This generator is used to create a standard class stack for a new entity.

### Install

```bash
    composer require ronasit/laravel-entity-generator: 1.*
```

If you're on Laravel 5.5 or later the package will be auto-discovered.
Otherwise you will need to manually configure it in your config/app.php.

```php
'providers' => [
    // ...
    RonasIT\Support\EntityGeneratorServiceProvider::class,
],
```

And publish.

```bash
    php artisan vendor:publish
```

### Examples
```bash
    php artisan make:entity EntityName \ 
        -S required_string_field \
        --integer=not_required_integer_field \
        --boolean-required=required_boolean_field \
        -j data \
        -e AnotherEntityName
```

### Documentation 

artisan make:entity command - add new Entity to project.

Syntax: artisan make:entity [name] [flags]

[name] - Name of the Entity.

[flags] :

    -i|--integer                : Add integer field to entity.
    
    -I|--integer-required       : Add required integer field to entity. If you want to specify default value you have to do it manually.
    
    -f|--float                  : Add float field to entity.
    
    -F|--float-required         : Add required float field to entity. If you want to specify default value you have to do it manually.
    
    -s|--string                 : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.
    
    -S|--string-required        : Add required string field to entity. If you want to specify default value ir size you have to do it manually.
    
    -b|--boolean                : Add boolean field to entity.
    
    -B|--boolean-required       : Add boolean field to entity. If you want to specify default value you have to do it manually.
    
    -t|--timestamp              : Add timestamp field to entity.
    
    -T|--timestamp-required     : Add timestamp field to entity. If you want to specify default value you have to do it manually.
    
    -j|--json                   : Add json field to entity.
    
    
    -a|--has-one          : Set hasOne relations between you entity and existed entity.
    
    -A|--has-many         : Set hasMany relations between you entity and existed entity.
    
    -e|--belongs-to       : Set belongsTo relations between you entity and existed entity.
    
    -E|--belongs-to-many  : Set belongsToMany relations between you entity and existed entity.   
    
    
    --without-model       : Set this flag if you already have model for this entity. Command will find it. This flag is a lower priority than --only-model.
     
    --without-repository  : Set if you don't want to use Data Access Level. Created Service will use special trait for controlling entity. This flag is a lower priority than --without-repository.
     
    --without-service     : Set this flag if you don't want to create service.
     
    --without-controller  : Set this flag if you don't want to create controller. Automatically requests and tests will not create too.
     
    --without-migration  : Set this flag if you already have table on db. This flag is a lower priority than --only-migration.
    
    --without-requests    : Set this flag if you don't want to create requests to you controller.
    
    --without-factory     : Set this flag if you don't want to create factory.
    
    --without-tests       : Set this flag if you don't want to create tests. This flag is a lower priority than --only-tests.

    --without-seeder      : Set this flag if you don't want to create seeder.
    
    --only-model          : Set this flag if you want to create only model. This flag is a higher priority than --without-model, --only-migration, --only-tests and --only-repository.
     
    --only-repository     : Set this flag if you want to create only repository. This flag is a higher priority than --without-repository, --only-tests and --only-migration.
    
    --only-service        : Set this flag if you want to create only service.
    
    --only-controller     : Set this flag if you want to create only controller.
    
    --only-requests       : Set this flag if you want to create only requests.
    
    --only-migration     : Set this flag if you want to create only repository. This flag is a higher priority than --without-migration and --only-tests.
    
    --only-factory        : Set this flag if you want to create only factory. This flag is a higher priority than --without-factory.
    
    --only-tests          : Set this flag if you want to create only tests. This flag is a higher priority than --without-tests.
          
    --only-seeder         : Set this flag if you want to create only seeder.
    
## Release notes

### 1.3
Since 1.3 version you need to add to your config/entity-generator.php following data:

```php
    'paths' => [
        ... // your old data
        'seeds' => 'database/seeds',
        'database_seeder' => 'database/seeds/DatabaseSeeder.php',
        'translations' => 'resources/lang/en/validation.php'
    ],
    'stubs' => [
        ... // your old data
        'empty_factory' => 'entity-generator::empty_factory',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'validation' => 'entity-generator::validation',
        'seeder' => 'entity-generator::seeder',
        'database_empty_seeder' => 'entity-generator::database_seed_empty'
    ]
``` 
