# Laravel-Entity-Generator

Laravel-Entity-Generator - This generator is used to create a standard class stack for a new entity.

### Install

We're highly recommending to install package for only dev environment

```bash
    composer require ronasit/laravel-entity-generator --dev
```

If you're on Laravel 5.5 or later the package will be auto-discovered.
Otherwise, you will need to manually configure it in your config/app.php.

```php
'providers' => [
    // ...
    RonasIT\Support\EntityGeneratorServiceProvider::class,
],
```

For dev installation provider should be registered optionally

```php
class AppServiceProvider
{
    public function boot(): void
    {
        // ...
        if (config('app.env') === 'local') {
            App::register(\RonasIT\Support\EntityGeneratorServiceProvider::class);
        }
    }
}
```

And publish.

```bash
    php artisan vendor:publish --provider="RonasIT\Support\EntityGeneratorServiceProvider"
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

`make:entity` artisan command - generate stack of classes to work with the new entity in project.

Syntax: 

```bash
> php artisan make:entity [entity-name] [options]
```

`entity-name` - Name of the Entity, recommended to use `CamelCase` naming style e.g. `WhitelistedDomain`

`options` - one or more options from the lists below

#### Fields definition options

    -i|--integer               : Add integer field to entity.

    -I|--integer-required      : Add required integer field to entity. If you want to specify default value you have to do it manually.

    -f|--float                 : Add float field to entity.

    -F|--float-required        : Add required float field to entity. If you want to specify default value you have to do it manually.

    -s|--string                : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.

    -S|--string-required       : Add required string field to entity. If you want to specify default value ir size you have to do it manually.

    -b|--boolean               : Add boolean field to entity.

    -B|--boolean-required      : Add boolean field to entity. If you want to specify default value you have to do it manually.

    -t|--timestamp             : Add timestamp field to entity.

    -T|--timestamp-required    : Add timestamp field to entity. If you want to specify default value you have to do it manually.

    -j|--json                  : Add json field to entity.

#### Relations definitions options

    -a|--has-one               : Set hasOne relations between you entity and existed entity.

    -A|--has-many              : Set hasMany relations between you entity and existed entity.

    -e|--belongs-to            : Set belongsTo relations between you entity and existed entity.

    -E|--belongs-to-many       : Set belongsToMany relations between you entity and existed entity.   

#### Single class generation mode options

    --only-model               : Set this flag if you want to create only model. This flag is a higher priority than --only-migration, --only-tests and --only-repository.

    --only-repository          : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests and --only-migration.

    --only-service             : Set this flag if you want to create only service.

    --only-controller          : Set this flag if you want to create only controller.

    --only-requests            : Set this flag if you want to create only requests.

    --only-migration           : Set this flag if you want to create only repository. This flag is a higher priority than --only-tests.

    --only-factory             : Set this flag if you want to create only factory.

    --only-tests               : Set this flag if you want to create only tests.

    --only-seeder              : Set this flag if you want to create only seeder.

    --only-resource            : Set this flag if you want to create only resource.

#### Mode combination options

    --only-entity              : Generate stack of classes to work with entity inside the app (Migration/Model/Service/Repository)

    --only-api                 : Generate stack of classes to implement API part to work with entity (routes/Controller/Requests/Tests)

#### Additional generation options

    --methods=[default: CRUD]  : Don't work for `--only-entity` option. Will generate API classes (routes, controller's
                                 methods, requests, tests) only for choosed methods.
                                 C - created
                                 R - read (search and get by id)
                                 U - update
                                 D - delete

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
