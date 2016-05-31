<?php

namespace Jacobcyl\ViewCounter\Commands;

use Illuminate\Console\Command;
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
        $this->info('Display this on the screen');
        $counterSync = new CounterSync();
        $counterSync->syncAll('view');
    }
}
