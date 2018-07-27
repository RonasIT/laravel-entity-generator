#Laravel-Entity-Generator

Laravel-Entity-Generator - This generator is used to create a standard class stack for a new entity.

### Install

Add to composer.json
```bash
    "ronasit/laravel-entity-generator": "1.*", 
```

Or

```bash
    composer require ronasit/laravel-entity-generator: 1.*
```

Then

```bash
    composer update
```

Add `RonasIT\Support\EntityGeneratorServiceProvider::class` to `app/config/app.php`, 
into 'providers' variable. And publish.

```bash
    php artisan vendor:publish
```

### Examples
```bash
    php artisan make:entity EntityName -S entityName
```

### Documentation 

artisan make:entity command - add new Entity to project.

Syntax: artisan make:entity [name] [flags]

[name] - Name of the Entity.

[flags] :

    --i   : Add integer field to entity.
    
    --I   : Add required integer field to entity. If you want to specify default value you have to do it manually.
    
    --f   : Add float field to entity.
    
    --F   : Add required float field to entity. If you want to specify default value you have to do it manually.
    
    --s   : Add string field to entity. Default type is VARCHAR(255) but you can change it manually in migration.
    
    --S   : Add required string field to entity. If you want to specify default value ir size you have to do it manually.
    
    --b   : Add boolean field to entity.
    
    --B   : Add boolean field to entity. If you want to specify default value you have to do it manually.
    
    --t   : Add boolean field to entity.
    
    --T   : Add boolean field to entity. If you want to specify default value you have to do it manually.
    
    --j   : Add json field to entity.
    
    --a   : Set hasOne relations between you entity and existed entity.
    
    --A   : Set hasMany relations between you entity and existed entity.
    
    --e   : Set belongsTo relations between you entity and existed entity.
    
    --E   : Set belongsToMany relations between you entity and existed entity.
    
    --without-model       : Set this flag if you already have model for this entity. Command will find it. This flag is a lower priority than --only-model.
     
    --without-repository  : Set if you don't want to use Data Access Level. Created Service will use special trait for controlling entity. This flag is a lower priority than --without-repository.
     
    --without-service     : Set this flag if you don't want to create service.
     
    --without-controller  : Set this flag if you don't want to create controller. Automatically requests will not create too.
     
    --without-migrations  : Set this flag if you already have table on db. This flag is a lower priority than --only-migrations.
    
    --without-requests    : Set this flag if you don't want to create requests to you controller.
    
    --without-factory     : Set this flag if you don't want to create factory.
    
    --without-tests       : Set this flag if you don't want to create tests. This flag is a lower priority than --only-tests.
    
    --only-model          : Set this flag if you want to create only model. This flag is a higher priority than --without-model, --only-migrations, --only-tests and --only-repository.
     
    --only-repository     : Set this flag if you want to create only repository. This flag is a higher priority than --without-repository, --only-tests and --only-migrations.
    
    --only-service        : Set this flag if you want to create only service.
    
    --only-controller     : Set this flag if you want to create only controller.
    
    --only-requests       : Set this flag if you want to create only requests.
    
    --only-migrations     : Set this flag if you want to create only repository. This flag is a higher priority than --without-migrations and --only-tests.
    
    --only-factory        : Set this flag if you want to create only factory. This flag is a higher priority than --without-factory.
    
    --only-tests          : Set this flag if you want to create only tests. This flag is a higher priority than --without-tests.
          