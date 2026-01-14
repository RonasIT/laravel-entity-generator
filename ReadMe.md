# Laravel-Entity-Generator

[![Coverage Status](https://coveralls.io/repos/github/RonasIT/laravel-entity-generator/badge.svg?branch=master)](https://coveralls.io/github/RonasIT/laravel-entity-generator?branch=master)

Laravel-Entity-Generator - This generator is used to create a standard class stack for a new entity.

## Installation

Install package using `dev` mode

```bash
composer require ronasit/laravel-entity-generator --dev
```

Publish package's resources.

```bash
php artisan vendor:publish --provider="RonasIT\Support\EntityGeneratorServiceProvider"
```

## Usage

### Entity generation

Call `make:entity` artisan command to run the generation command for the full stack of the entity classes:

```bash
php artisan make:entity Post
```

Entity name may contain the subfolder, in this case generated `model` and `nova resource` will be placed to
the subfolder as well:

```bash
php artisan make:entity Forum/Blog/Post
```

#### Fields definition options

The `make:entity` provides an ability to set the entity's fields, which will be used in all created classes (e.g. Model, Create/Update requests, Test fixtures, etc.)

```bash
php artisan make:entity Post -S title -S text -t published_at
```

The following field types are available to use:

| Type | Modificator | Short Option | Full Option |
| -------- | -------- | ------- | ------- |
| `integer` | | `-i` | `--integer` |
| `integer` | required | `-I` | `--integer-required` |
| `float` | | `-f` | `--float` |
| `float` | required | `-F` | `--float-required` |
| `string` | | `-s` | `--string` |
| `string` | required | `-S` | `--string-required` |
| `boolean` | | `-b` | `--boolean` |
| `boolean` | required | `-B` | `--boolean-required` |
| `datetime` | | `-t` | `--timestamp` |
| `datetime` | required | `-T` | `--timestamp-required` |
| `json` | | `-j` | `--json` |

#### Relations definitions options

Command also provides an ability to set relations, which will be added to the model

```bash
php artisan make:entity Post -A Comment -A Reaction
```

Relation may be placed in the subfolder, in this case - set the relative namespace from the model directory:

```bash
php artisan make:entity Post -A Blog/Comment -A Forum/Common/Reaction
```

The following options are available to set relations:

| Type  | Short Option | Full Option |
| -------- | ------- | ------- |
| Has one | `-a` | `--has-one` |
| Has many | `-A` | `--has-many` |
| Belongs to | `-e` | `--belongs-to` |
| Belongs to many | `-E` | `--belongs-to-many` |

#### Single class generation mode options

Command allows to generate only single entity-related class

```bash
php artisan make:entity Post --only-tests
```

The following options are available:

- `--only-model`
- `--only-repository`
- `--only-service`
- `--only-controller`
- `--only-requests`
- `--only-migration`
- `--only-factory`
- `--only-tests`
- `--only-seeder`
- `--only-resource`
- `--only-nova-tests`

#### Mode combination options

Sometimes you need to generate the stack of classes to work with some entity only inside the application without
the ability to manipulate it using API, or you need to create only API for already existed entity.

For this task, there are two mutually exclusive options:

1. `--only-entity`, generate stack of classes to work with entity only inside the app, it includes:
- `migration`
- `model`
- `service`
- `repository`

2. `--only-api`, generate stack of classes to implement API part to work with already existed entity:
- `routes`
- `controller`
- `requests`
- `tests`

#### Methods setting options

By default, the command generate all methods from the CRUD stack which includes:
- create
- update
- delete
- search
- get by id

But what if we need to create the interface only for updating and reading the entity? Just use the `methods` option for it:

```bash
php artisan make:entity Post --methods=RU
```

Feel free to use any combinations of actions in any order. Each action has its own character:
- `C` create
- `U` update
- `D` delete
- `R` read (search and get by id)

#### Special class-related options

- `--nova-resource-name` - Specifies the Nova resource name when generating a test.

## Release notes

### 1.3

Since 1.3 version you need to add to your config/entity-generator.php following data:

```php
    'paths' => [
        ... // your old data
        'database_seeder' => 'database/seeds/DatabaseSeeder.php',
        'translations' => 'lang/en/validation.php'
    ],
    'stubs' => [
        ... // your old data
        'empty_factory' => 'entity-generator::empty_factory',
        'translation_not_found' => 'entity-generator::translation_not_found',
        'validation' => 'entity-generator::validation',
        'seeder' => 'entity-generator::seeder',
        'database_empty_seeder' => 'entity-generator::database_empty_seeder'
    ]
``` 
