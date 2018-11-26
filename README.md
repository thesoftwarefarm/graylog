# Library for sending messages to Graylog - Laravel
 
Fluent interface for composing and sending messages to Graylog.

This package was designed to work in a standalone project or in a cluster of projects which push messages into a master project/database which act as a collector.

If you use this package in cluster mode, make sure the process `php artisan graylog:dispatch-jobs` is running on master project. This can be kept alive with `supervisor`

# Installation

Require this package in your `composer.json` and update composer. Run the following command:
```php
composer require tsfcorp/graylog
```

After updating composer, the service provider will automatically be registered and enabledusing Auto-Discovery

If your Laravel version is less than 5.5, make sure you add the service provider within `app.php` config file.

```php
'providers' => [
    // ...
    TsfCorp\Graylog\GraylogServiceProvider::class,
];
```

Next step is to run the artisan command to install config file and optionally migration file. The command will guide you through the process.

```php
php artisan graylog:install
```

Update `config/graylog.php` with your settings.
### Requirements
This package makes use of Laravel Queues/Jobs to send a message to Graylog. Make sure the queue system is configured properly

# Usage Instructions

```php
use TsfCorp\Graylog\GraylogMessage;

$message = (new GraylogMessage)
    ->setLevel(GraylogMessage::ERROR)
    ->setShortMessage('Short message.')
    ->setFullMessage('Full message.')
    ->setSubsystem('subsystem')
    ->setAdditional('custom_1', 'value_1')
    ->setAdditional('custom_2', 'value_2')
    ->setContext([
        'prop' => 'value'
    ]);
``` 
Use `enqueue()` method to save the message in database without sending to Graylog. Useful when you want to just save the message but delay sending. Or when `database_connection` config value is another database and sending is performed from there.

```php
$message->enqueue();
```

Save the message and schedule a job to send the message to graylog
```php
$message->enqueue()->dispatch();
```