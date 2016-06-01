<?php
return [
    /**
     * count every request?
     * if set true, It will count every request of specified object
     * else It will count between the viewCountDuration
     */
    'isViewCountEveryTime' => false,

    /**
     * count between duration if isViewCountEveryTime is set false
     */
    'viewCountDuration' => 60,//count view duration (minutes)

    'syncClasses' => [

    ]
];