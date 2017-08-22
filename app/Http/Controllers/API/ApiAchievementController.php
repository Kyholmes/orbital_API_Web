<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Achievement;
use App\Transformer\AchievementTransformer;
use Request;
use Validator;
use Input;

class ApiAchievementController extends ApiController
{
    //

    public function __construct()
    {
    	$this->middleware('api_auth');

    	$this->middleware('token_auth');

    	parent::__construct();
    }

    public function get()
    {
    	$get_nus_id = (new AuthKeyController)->get_nus_id('auth-key');

    	$get_achievement = Achievement::where('nus_id', $get_nus_id)->first();

    	if($get_achievement == null)
    	{
    		$addSucess = ApiAchievementController::addNewAchievement($get_nus_id);

    		if(!$addSucess)
    		{
    			return $this->errorInternalError('server down');
    		}
    	}

    	return $this->respondWithItem($get_achievement, new AchievementTransformer, 'achievement');
    }

    public static function updateAchievement($achievementType, $value, $nus_id)
    {
    	$get_achievement = Achievement::where('nus_id', $nus_id)->first();

    	if($get_achievement == null)
    	{
    		$addAchievementSucess = ApiAchievementController::addNewAchievement($nus_id);

    		if(!$addAchievementSucess)
    		{
    			return false;
    		}
    	}

    	switch ($achievementType) {
    		//update question no & points
    		case 1:
    			$get_achievement->question_no = $get_achievement->question_no + $value;

                $get_achievement->points = $get_achievement->points + ($value * 10);
    			break;

            //update points (vote/downvote)
            case 2:
                $get_achievement->points = $get_achievement->points + $value;
                break;
    		
            //update comment no & points
            case 3:
                $get_achievement->comment_no = $get_achievement->comment_no + $value;
                $get_achievement->points = $get_achievement->points + ($value * 5);
                break;

            //update best answer no & points
            case 4:
                $get_achievement->answer_no = $get_achievement->answer_no + $value;
                $get_achievement->points = $get_achievement->points + ($value * 5);
                break;

    		default:
    			break;
    	}

    	$updateSuccess = $get_achievement->save();

    	if($updateSuccess)
    	{
    		return true;
    	}

    	return false;
    }

    public static function addNewAchievement($nus_id)
    {
    	$get_achievement = new Achievement();

		$get_achievement->nus_id = $nus_id;

		$addSucess = $get_achievement->save();

		if(!$addSucess)
		{
			return false;
		}
		
		return true;
		
    }
}
