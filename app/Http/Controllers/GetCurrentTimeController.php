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
}
