# Laravel Activity Logger
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
ENABLE_LOGGING=true
LOGGING_QUERY=false
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

or you can add middleware class on kernel application

```php
\AdityaDarma\LaravelDatabaseLogging\Middleware\CaptureLogging::class
```

This middleware can be enabled/disabled in the configuration settings.

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

##### Laravel Activity Dashboard Routes

Set route access from file config `database-logging.php` 

* ```/database-logging```

## License

This Package is licensed under the MIT license. Enjoy!
