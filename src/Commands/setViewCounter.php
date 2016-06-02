<?php

namespace Jacobcyl\ViewCounter\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Jacobcyl\ViewCounter\CounterSync;

class SetViewCounter extends Command
{
    protected $options = [
        'plus', 'minus'
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'counter:view {classname} {amount} {--action=plus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'change the counter by action';

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
        $className = $this->argument('classname');
        $amount = $this->argument('amount');
        $action = $this->option('action');
        if(!in_array($action, $this->options)){
            $this->error('action not found!');
            return false;
        }

        $this->info('will '.$action.' '.$amount.' of every view counter');
        $countSync = new CounterSync();
        $countSync->setViewCountBatch($className, $action, $amount);
    }
}
