# Laravel Database Logging

[![Tests](https://github.com/adityadarma/laravel-database-logging/workflows/Tests/badge.svg)](https://github.com/adityadarma/laravel-database-logging/actions)
[![Latest Stable Version](https://poser.pugx.org/adityadarma/laravel-database-logging/v/stable)](https://packagist.org/packages/adityadarma/laravel-database-logging)
[![License](https://poser.pugx.org/adityadarma/laravel-database-logging/license)](https://packagist.org/packages/adityadarma/laravel-database-logging)

Laravel Database Logging is a feature that allows developers to store application logs in a database, rather than the default file-based storage. This feature provides a structured and organized approach to managing application logs, making it easier to query and analyze them.

### Laravel Installation Instructions
1. From your projects root folder in terminal run:

    ```bash
    composer require adityadarma/laravel-database-logging
    ```

2. Install config and asset to record the activities to:

    ```bash
    php artisan database-logging:install
    ```

3. Run the migration to add the table to record, before running please check morph key type on config to set type column:
   
   *Note: Before migrate, please check config `/config/database-logging.php` type data on morp relation

    ```bash
    php artisan migrate
    ```

### Configuration
Laravel Database Logging can be configured in directly in `/config/database-logging.php` if you published the assets.
Or you can variables to your `.env` file.


##### Environment File
Here are the `.env` file variables available:

```dotenv
DB_CONNECTION_LOGGING=logging # remove if same connection
ENABLE_LOGGING=true
QUERY_LOGGING=false
DURATION_LOGGING=30
```

##### Separate logging database

Point `DB_CONNECTION_LOGGING` at any connection defined in `config/database.php`.
The `database_loggings` table is created on that connection, and the log rows
keep their own copy of the actor name in `user_name`, so the dashboard renders
correctly even though the logging database holds no `users` table.

```php
// config/database.php
'connections' => [
    'logging' => [
        'driver' => 'mysql',
        'host' => env('DB_LOGGING_HOST', '127.0.0.1'),
        'database' => env('DB_LOGGING_DATABASE', 'logging'),
        'username' => env('DB_LOGGING_USERNAME', 'forge'),
        'password' => env('DB_LOGGING_PASSWORD', ''),
        // ...
    ],
],
```

Which column holds the display name is set per model in `database-logging.php`:

```php
'model' => [
    App\Models\User::class => 'name',
],
```

### Usage

##### Middleware Usage
Events for laravel authentication scaffolding are listened for as providers and are enabled via middleware.
You can add events to your routes and controllers via the middleware:

```
capture-logging
```

Example to start recording page views using middleware in `web.php`:

```php
Route::group(['middleware' => ['web', 'capture-logging']], function () {
    Route::get('/', 'WelcomeController@welcome')->name('welcome');
});
```

This middleware can be enabled/disabled in the configuration settings.

##### Trait Usage
Events can be recorded directly by using the trait.
When using the trait you can customize the event description.

To use the trait:
1. Include the call in the head of your class file:

    ```php
    use \AdityaDarma\LaravelDatabaseLogging\Traits\DatabaseLoggable;
    ```

2. Include the trait call in the opening of your class:

    ```php
    use DatabaseLoggable;
    ```

### Routes

##### Laravel Activity Dashboard Routes

Set route access from file config `database-logging.php`

* ```/database-logging```

### Purge

##### Remove data logger

Set limit days data log from file config `database-logging.php` then run the command

* ```database-logging:purge```

## License

This Package is licensed under the MIT license. Enjoy!
