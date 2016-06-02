  
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
add "use ViewCounterTrait;" to your model
```php  
    $model->view()
 ```

##add schedule task
edit app/Console/Kernel.php file's schedule method.add **counter:sync** command:
```php
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')->hourly();
        $schedule->command('counter:sync')->dailyAt('23:50');
    }
```

> run crontab -e  then add follow

    * * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
    
    
