<?php

namespace Jacobcyl\ViewCounter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Jacobcyl\ViewCounter\CounterSync;

class SyncCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counter:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize counter from redis to mysql';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Sync counter date to database');
        $counterSync = new CounterSync();
        $counterSync->syncAll();
        /*$classNames = Config::get('counter.syncClasses');
        foreach($classNames as $className){
            $counterSync->syncAll($className);
        }*/
        $this->info('Sync finished at '. date('Y-m-d'));
        Log::info('Sync finished at '. date('Y-m-d'));
    }
}
