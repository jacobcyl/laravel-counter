<?php namespace Jacobcyl\ViewCounter;

/**
 * Created by jacobcyl.
 * Date: 2016/5/30 0030
 * Time: 下午 15:25
 */

use Illuminate\Support\Facades\Log;
use Jacobcyl\ViewCounter\Models\Counter;
use Jacobcyl\ViewCounter\Models\UserCounter;
use Cache;

class CounterSync
{

    /**
     * CounterSync constructor.
     */
    public function __construct()
    {
    }

    public function syncAll($className = null){
        $userCounters = UserCounter::ofClassName($className)->get();
        $objects = $userCounters->unique(function($item){
            return $item['class_name'].$item['action'].$item['object_id'];
        })->values()->all();

        foreach($objects as $object){
            $this->sync($object);
        }
    }

    public function sync($object){
        $cacheName = $this->getCacheName($object);
        try{
            $count = Cache::get($cacheName);
            if(!empty($count)){
                $countType = $object->action.'_counter';
                Counter::updateOrCreate(
                    [
                        'class_name'    => $object->class_name,
                        'object_id'     => $object->object_id,
                        'count_date'    => date('Y-m-d')
                    ],
                    [
                        $countType      => $count,
                        'count_date'    => date('Y-m-d')
                    ]
                );
                return true;
            }else{
                return false;
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return false;
        }
    }

    public function setViewCountBatch($className, $action, $amount){
        $userCounters = UserCounter::ofClassName($className)->where('action', 'view')->get();

        foreach($userCounters as $object){
            $cacheName = $this->getCacheName($object);
            Log::debug($cacheName.' '.$amount);
            switch($action){
                case 'plus':
                    $count = Cache::increment($cacheName, $amount);
                    break;
                case 'minus':
                    $count = Cache::decrement($cacheName, $amount);
                    if($count<0)
                        Cache::put($cacheName, 0);
                    break;
            }
        }

        return false;
    }

    private function getCacheName($object){
        return $object->class_name . ':' . $object->object_id . ':' . $object->action . 's';
    }
}