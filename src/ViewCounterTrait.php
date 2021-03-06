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
use Jacobcyl\ViewCounter\Models\Counter;

trait ViewCounterTrait{
    protected $counter;
    protected $cacheViewName;
    protected $cacheLikeName;

    /**
     * count the object`s view
     * @return views_count
     */
    public function view(){
        $viewsCount = $this->views_count();

        if ( Config::get('counter.isViewCountEveryTime') ){
            //$viewCount = Cache::increment($this->cacheViewName, Config::get('counter.viewIncrementAmount', 1));
            $viewsCount = $this->incView();
            //$this->setViewed();
        }else if ( !$this->isViewed() ){
            $viewsCount = $this->incView();
            //$this->setViewed();
        }

        return $viewsCount;
    }

    public function like(){
        $likeKey = $this->getLikeKey();
        $likeCount = $this->likes_count();
        if ( !$this->isLiked() ){
            if ( Auth::check() ){
                $this->recordUser('like');
            } else {
                session([$likeKey=>time()]);
            }
            $res = Cache::increment($this->cacheLikeName, 1);
        }
        return true;
    }

    public function unlike(){
        $likeKey = $this->getLikeKey();
        $likeCount = $this->likes_count();
        if ( !$this->isLiked() ){
            return false;
        }
        if ( Auth::check() ){
            $isLiked = $this->user_counters()->where('user_id', Auth::user()->id)->where('action', 'like')->delete();
        } else {
            session([$likeKey=>null]);
        }
        $res = Cache::decrement($this->cacheLikeName, 1);

        return true;
    }

    /**
     * toggle like
     * @return bool isLiked
     */
    public function toggleLike(){
        if ( !$this->isLiked() ){
            $this->like();
        }else{
            $this->unlike();
        }
        return $this->isLiked();
    }

    /**
     * return the counter object of the model
     */
    public function counter(){
        if(!isset($this->counter))
        {
            $class_name = $this->getClassName();
            $this->counter = Counter::where('class_name', $class_name)->where('object_id', $this->id)->orderBy('count_date', 'desc')->first();
            if( !$this->counter ){
                $this->counter = Counter::create(array('class_name' => $class_name, 'object_id' => $this->id, 'count_date'=>date('Y-m-d')));
            }
        }
        return $this->counter;
    }

    public function user_counters()
    {
        return $this->hasMany('Jacobcyl\ViewCounter\Models\UserCounter', 'object_id')->where('class_name', $this->getClassName());
    }

    public function views_count(){
        $this->cacheViewName = $this->generateName('views');
        return Cache::rememberForever(
            $this->cacheViewName,
            function(){
                $counter = $this->counter();

                return $counter->view_counter ? $counter->view_counter : Config::get('counter.viewStartNumber', 0) ;
            }
        );
    }

    public function likes_count() {
        $this->cacheLikeName = $this->generateName('likes');
        return Cache::rememberForever(
            $this->cacheLikeName,
            function(){
                $counter = $this->counter();
                return $counter->like_counter ? $counter->like_counter : Config::get('counter.likeStartNumber', 0) ;
            }
        );
    }

    /**
     * record users who with action
     * @param $action
     */
    private function recordUser($action) {
        $data = array(
            'class_name'    => $this->getClassName(),
            'object_id'     => $this->id,
            'user_id'       => Auth::user()->id,
            'action'        => $action
        );
        $this->user_counters()->updateOrCreate($data, $data);
    }

    /**
     * increase view count
     */
    private function incView() {
        if ( !$this->cacheViewName ){
            $viewsCount = $this->views_count();
        }
        $viewsCount = Cache::increment($this->cacheViewName, Config::get('counter.viewIncrementAmount', 1));
        $this->setViewed();

        $this->regularCheck($viewsCount, 'view');
        return $viewsCount;
    }

    /**
     * Set the user has viewed
     *
     * @return bool
     */
    private function setViewed(){
        if ( !$this->isViewed() ){
            $viewKey = $this->getViewKey();

            if ( Auth::check() ){ //user had login, record user action
                Cache::put($viewKey.':user:'.Auth::user()->id, time(), Config::get('counter.viewCountDuration'));
                $this->recordUser('view');
            } else { //guest. use session
                session([$viewKey=>time()]);
            }

            return true;
        }

        return false;
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
                $viewed = (time() - $time) < config('counter.viewCountDuration') * 60;
            } else {
                $viewed = false;
            }
            return $viewed;
        }
    }

    /**
     * check whether is liked
     * return true|false
     */
    public function isLiked(){
        $likeKey = $this->getLikeKey();
        if ( Auth::check() ){
            $isLiked = $this->user_counters()->where('user_id', Auth::user()->id)->where('action', 'like')->count();
            return $isLiked;
        } else {
            $isLiked = session($likeKey);
            return !empty($isLiked);
        }
    }

    private function regularCheck($count, $action){
        if ( Auth::check() ){
            $threshold = config('counter.checkThreshold', 10);
            if ( $count >= $threshold && $count % $threshold == 0  ){
                $this->recordUser($action);
            }
        }
    }

    private function getViewKey(){
        return 'viewed:' . $this->getClassName() . ':' . $this->id;
    }

    private function getLikeKey(){
        return 'liked:' . $this->getClassName() . ':' . $this->id;
    }

    private function generateName($action){
        return $this->getClassName() . ':' . $this->id . ':' . $action;
    }

    private function getClassName(){
        return snake_case(join('', array_slice(explode('\\', get_class($this)), -1)));
    }
}