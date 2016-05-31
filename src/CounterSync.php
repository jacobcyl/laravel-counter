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

    public function syncAll($action){
        $objects = UserCounter::where('action', $action)->get();
        foreach($objects as $object){
            $this->sync($object);
        }
    }

    public function sync($object){
        $cacheName = $this->getCacheName($object);
        try{
            $count = Cache::get($cacheName);
            if(!empty($count)){
                Counter::where('class_name', $object->class_name)
                        ->where('object_id', $object->object_id)
                        ->update([$object->action.'_counter'=>$count]);
                return true;
            }
            return false;
        }catch(\Exception $e){
            Log::error($e->getMessage());
            return false;
        }
    }

    private function getCacheName($object){
        return $object->class_name . ':' . $object->object_id . ':' . $object->action . 's';
    }
}