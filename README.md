# Laravel Activity Logger
Laravel Database Logging is a feature that allows developers to store application logs in a database, rather than the default file-based storage. This feature provides a structured and organized approach to managing application logs, making it easier to query and analyze them.

### Laravel Installation Instructions
1. From your projects root folder in terminal run:

```bash
    composer require adityadarma/laravel-database-logging
```

2. Install config and asset to record the activities to:

```php
    php artisan database-logging:install
```

3. Run the migration to add the table to record the activities to:

```php
    php artisan migrate
```

### Configuration
Laravel Database Logging can be configured in directly in `/config/database-logging.php` if you published the assets.
Or you can variables to your `.env` file.


##### Environment File
Here are the `.env` file variables available:

```dotenv
ENABLE_LOGGING=true
```

### Usage

##### Middleware Usage
Events for laravel authentication scaffolding are listened for as providers and are enabled via middleware.
You can add events to your routes and controllers via the middleware:

```php
capture-logging
```

Example to start recording page views using middlware in `web.php`:

```php
Route::group(['middleware' => ['web', 'capture-logging']], function () {
    Route::get('/', 'WelcomeController@welcome')->name('welcome');
});
```

This middlware can be enabled/disabled in the configuration settings.

##### Trait Usage
Events can be recorded directly by using the trait.
When using the trait you can customize the event description.

To use the trait:
1. Include the call in the head of your class file:

    ```php
        use AdityaDarma\LaravelDatabaseLogging\Traits\DatabaseLoggable;
    ```

2. Include the trait call in the opening of your class:

    ```php
        use DatabaseLoggable;
    ```

### Routes
##### Laravel Activity Dashbaord Routes

* ```/database-logging```

## License
Laravel-logger is licensed under the MIT license. Enjoy!
