<?php
namespace Jacobcyl\ViewCounter;

/**
 * Created by jacobcyl.
 * Date: 2016/5/30 0030
 * Time: 下午 15:10
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Jacobcyl\ViewCounter\Models\Counter;

trait ViewCounterTrait{
    //protected $isViewCountEveryTime = false; //count for every request
    //protected $viewCountDuration = 1;//count view duration (minutes)
    protected $counter;
    protected $cacheViewName;
    protected $cacheLikeName;

    public function view(){
        $viewCount = $this->views_count();
        if ( Config::get('counter.isViewCountEveryTime') ){
            Cache::increment($this->cacheViewName);
        }else{
            if (!$this->isViewed()){
                $this->incView();
            }
        }
    }

    public function like(){

    }

    public function unlike(){

    }

    /**
     * return the counter object of the model
     */
    public function counter(){
        if(!isset($this->counter))
        {
            $class_name = $this->getClassName();
            $this->counter = Counter::firstOrCreate(array('class_name' => $class_name, 'object_id' => $this->id));
        }
        return $this->counter;
    }

    public function user_counter()
    {
        return $this->hasOne('Jacobcyl\ViewCounter\Models\UserCounter', 'object_id')->where('class_name', $this->getClassName());
    }

    public function views_count(){
        $this->cacheViewName = $this->generateName('views');
        return Cache::rememberForever(
            $this->cacheViewName,
            function(){
                $counter = $this->counter();

                return $counter->view_counter ? $counter->view_counter : 0 ;
            }
        );
    }

    public function links_count() {
        $this->cacheLikeName = $this->generateName('likes');
        return Cache::rememberForever(
            $this->cacheLikeName,
            function(){
                $counter = $this->counter();

                return $counter->like_counter ? $counter->like_counter : 0 ;
            }
        );
    }

    private function recordUser($action) {
        $data = array(
            'class_name'    => $this->getClassName(),
            'object_id'     => $this->id,
            'user_id'       => Auth::user()->id,
            'action'        => $action
        );
        $this->user_counter()->updateOrCreate([], $data);
    }

    /**
     * check whether is viewed
     * return true|false
     */
    private function isViewed() {
        $viewKey = $this->getViewKey();

        if ( Auth::check() ) {
            $viewed = Cache::get($viewKey.':user:'.Auth::user()->id);
            return !empty($viewed);
        } else {
            $time = session($viewKey);
            if ( !empty($time) ) {
                $viewed = (time() - $time) < Config::get('counter.viewCountDuration') * 60;
            } else {
                $viewed = false;
            }
            return $viewed;
        }
    }

    /**
     * increase view count
     */
    private function incView() {
        $viewKey = $this->getViewKey();

        if ( Auth::check() ){ //user had login, record user action
            Cache::put($viewKey.':user:'.Auth::user()->id, time(), Config::get('counter.viewCountDuration'));
            $this->recordUser('view');
        } else { //guest. use session
            session([$viewKey=>time()]);
        }
        Cache::increment($this->cacheViewName);
    }

    /**
     * check whether is liked
     * return true|false
     */
    private function isLiked(){

    }

    private function getViewKey(){
        return 'viewed:' . $this->getClassName() . ':' . $this->id;
    }

    private function generateName($action){
        return $this->getClassName() . ':' . $this->id . ':' . $action;
    }

    private function getClassName(){
        return snake_case(join('', array_slice(explode('\\', get_class($this)), -1)));
    }
}