  
## Installation  
    composer require jacobcyl/view-counter:1.*
  
## Configuration  
add provider
```php
    Jacobcyl\ViewCounter\ViewCounterServiceProvider::class,
```
publish config file and migration files
```php
    php artisan vendor:publish 
    php artisan migrate
```
