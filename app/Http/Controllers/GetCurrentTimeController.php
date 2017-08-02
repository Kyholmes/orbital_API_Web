<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GetCurrentTimeController extends Controller
{
    //

    public static function getCurrentTime()
    {
    	date_default_timezone_set('Asia/Singapore');

    	$current_time = time();

    	$datetimeFormat = 'Y-m-d H:i:s';

    	$current = new \DateTime();

	    $current->setTimestamp($current_time);

	    return $current->format($datetimeFormat);
    }

    public static function getExpiredTime($current_time, $hour)
    {
        date_default_timezone_set('Asia/Singapore');

        $expired_time = time($current_time) + ($hour * 60 * 60);

        $datetimeFormat = 'Y-m-d H:i:s';

        $expired = new \DateTime();

        $expired->setTimestamp($expired_time);

        return $expired->format($datetimeFormat);
    }
}
