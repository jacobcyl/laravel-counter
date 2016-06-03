<?php
return [
    /**
     * count every request?
     * if set true, It will count every request of specified object
     * else It will count between the viewCountDuration
     */
    'isViewCountEveryTime' => false,

    /**
     * count between duration (minutes) if isViewCountEveryTime is set false
     */
    'viewCountDuration' => 60,

    /**
     * view incrementing amount
     */
    'viewIncrementAmount' => 1,

    /**
     * number of a object start to count (init object counter)
     */
    'viewStartNumber' => 0,

    /**
     * regular check threshold to sync user_counter table
     */
    'checkThreshold' => 20,
];