#  OneSignal Push Notifications for Laravel 5

## Introduction

This is a rehash from the original Berkayk package. 

## Installation

First, you'll need to require the package with Composer:

```sh
composer require jonlod/onesignal-laravel
```


Auto discovery is on. if you use Laravel 5.4 or lower => 

<---------- <=5.4 

 update `config/app.php` by adding an entry for the service provider.

```php
'providers' => [
	// ...
	jonlod\OneSignal\OneSignalServiceProvider::class
];
```


Then, register class alias by adding an entry in aliases section

```php
'aliases' => [
	// ...
	'OneSignal' => jonlod\OneSignal\OneSignalFacade::class
];
```
---------> <=5.4 

Finally, from the command line again, run 

```
php artisan vendor:publish --provider=jonlod/OneSignal/OneSignalServiceProvider --tag=config
``` 

to publish the default configuration file. 
This will publish a configuration file named `onesignal.php`.



## Configuration

 Keys should be set in the .env

```php

ONESIGNAL_APP_ID=<*****>
ONESIGNAL_REST_API_KEY=<*******>
ONESIGNAL_EMAIL_TOGGLE= //true or false
```

The email toggle is required if you use hashed emails. Otherwise the tag user_id is used. 

Tomorrow hour can be changed in the onesignal.php config file. This is only used for delayed push notifications on the next day.

## Usage

Include the trait anywhere. 

```php
use PushNotifications;
```

### Sending a Notification To All Users

```php
$this->pushToAll(...);
```

### Sending a Notification To All Users Tomorrow

```php
$this->pushToAllTomorrow(...);
```

### Sending a Notification based on Tags/Filters


```php
$this->pushToAllTag(...);
```

### Sending a Notification To A Specific User


```php
$this->pushToUser(...);
```


### Async

All pushes use the job: SendPushes. This is automatically queued if a queue is available.
