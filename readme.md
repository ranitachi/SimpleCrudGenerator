
# SimpleCrudGenerator

[![Latest Stable Version](https://poser.pugx.org/ranitachi/simple-crud-generator/v/stable)](https://packagist.org/packages/ranitachi/simple-crud-generator)
[![Total Downloads](https://poser.pugx.org/ranitachi/simple-crud-generator/downloads)](https://packagist.org/packages/ranitachi/simple-crud-generator)
[![License](https://poser.pugx.org/ranitachi/simple-crud-generator/license)](https://packagist.org/packages/ranitachi/simple-crud-generator)

## Introduction

**SimpleCrudGenerator** is a package designed to easily generate CRUD operations for your Laravel applications. With one command, you can generate models, migrations, controllers, services, and requests based on a database table name.

## Installation

You can install the package via Composer:

### Step 1: Install the Package

Run the following Composer command in your Laravel project:

```bash
composer require ranitachi/simple-crud-generator
```

### Step 2: Publish the Service Provider (optional)

In most cases, the service provider will be automatically discovered by Laravel. However, if you need to manually add it, include the service provider in your `config/app.php` file:

```php
'providers' => [
    // Other service providers
    Fcn\SimpleCrudGenerator\SimpleCrudGeneratorServiceProvider::class,
];
```

### Step 3: Generate CRUD Files

Once installed, you can generate the CRUD files using the following Artisan command:

```bash
php artisan make:simple-crud {table_name}
```

Replace `{table_name}` with the name of the database table for which you want to generate the CRUD files.

For example:

```bash
php artisan make:simple-crud posts
```

This will generate the following:
- Migration file for the `posts` table.
- Model with `SoftDeletes`.
- Controller with basic CRUD operations.
- Service to handle business logic.
- Request class for validation.

## Usage Example

After running the generator command, you can immediately start using the generated files.

- **Model**: The model will be located at `app/Models/Post.php` if your table is named `posts`.
- **Controller**: The controller will be generated in `app/Http/Controllers/PostController.php`.
- **Service**: The service class will be in `app/Services/PostService.php`, responsible for handling business logic.
- **Request**: The request file will be generated at `app/Http/Requests/PostRequest.php` and can be used for validation.

### Routes Example:

You can define the route in `routes/web.php` to use the generated controller:

```php
Route::resource('posts', App\Http\Controllers\PostController::class);
```

## Features

- Automatically generates migration, model, controller, request, and service files based on a given table name.
- Supports soft deletes by default in generated models.
- Generates RESTful controllers with all CRUD methods.
- Includes basic validation via Request classes.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
