# Api Generator for Laravel

A basic laravel package that generates models, resources, controllers, form requests, factories, enums and typescript interfaces from an existing database (with relationships).

## Installation

```sh
    composer require zakalajo/api-generator --dev
```

## Utilities

The package can generate various components needed in a Laravel application, you can generate them for a single table or for the entire database at once.

### scaff:model

The scaff:model command generates a new model.

It accepts a table name as an argument or you can provide the --all option to generate models for the entire database, the package will generate "belongs to" and "has many" relationships.

By default, if there's an existing model with the same name the package will not override it, if you want to override the existing model you need to provide the --override option or -O for short.

This is the case for other commands that generates resources, controllers...

### scaff:controller

The scaff:controller command generates a new controller, like the model it accepts a table name or the (--all) option.

### scaff:resource

The scaff:resource command generates a new resource

### scaff:request

The scaff:request command generates a new form request

### scaff:factory

The scaff:factory command generates a new factory.

### scaff:api

The scaff:api will generate all of the components needed for an api:

-   Model
-   Factory
-   Resource
-   Form request
-   Controller

You can provide a table name to generate the components for a single table or the --all option to generate for the entire database.

Alternatively, you can provide the --enums option and the package will generate all the enums found in table columns, or you can provide the --types option to generate Typescript types.

Note:
The generated enums will be found in the app\Enums folder.
The generated types will be found in the types\index.ts file.

### scaff:user

The scaff:user will generate a random user using the user Factory and outputs the email of the random user.
