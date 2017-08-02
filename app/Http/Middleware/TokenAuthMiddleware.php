<?php

namespace App\Http\Middleware;

use Closure;

use App\Access_Token;

use Response;

class TokenAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $header_item = $request->header();

        if(is_null($request->header('auth-key')))
        {
            return Response::json([
                'error' => [
                    'code' => 'UNAUTH',
                    'http_code' => 401,
                    'message' => 'Access key not found'
                ]], 401);
        }

        $auth_key = $header_item['auth-key'][0];

        //check if access token is valid
        $access_auth = Access_Token::where('token', $auth_key)->first();

        if(empty($access_auth))
        {
            return Response::json(['error' => [
                'code' => 'UNAUTH',
                'http_code' => 401,
                'message' => 'Access key invalid'
            ]], 401);
        }
        else
        {
            $code = TokenAuthMiddleware::check_token($access_auth);

            if($code == 401)
            {
                return Response::json(['error' => [
                    'code' => 'UNAUTH',
                    'http_code' => $code,
                    'message' => 'Access key expired'
                ]], $code);
            }
            else if($code == 500)
            {
                return Response::json(['error' => [
                    'code' => 'UNAUTH',
                    'http_code' => $code,
                    'message' => 'Internal network error'
                ]], $code);
            }
        }

        return $next($request);
    }

    //check if access token is expired
    public function check_token($key)
    {
        $now = time();

        $expired_date = strtotime($key->expired_date);
        // echo $now;

        // echo $expired_date;

        $message_code = 0;

        if($expired_date < $now)
        {
            $delete_token = Access_Token::where('token', $key->token)->delete();

            if($delete_token > 0)
            {
                $message_code = 401;
            }
            else
            {
                echo "delete failed";
                $message_code = 500;
            }
        }
        else if(($expired_date - $now) <= (8 * 60 * 60))
        {

            date_default_timezone_set('Asia/Singapore');

            $datetimeFormat = 'Y-m-d H:i:s';

            $expired = new \DateTime();

            $expired_date = $key->expired_date + (24 * 60 * 60);

            $expired->setTimestamp($expired_date);

            $update_token = Access_Token::where('token', $key->token)->first();

            $update_token->expired_date = $expired->format($datetimeFormat);

            $update_token->save();

            if(empty($update_token))
            {
                echo "update failed";
                $message_code = 500;
            }
        }

        return $message_code;
    }
}
