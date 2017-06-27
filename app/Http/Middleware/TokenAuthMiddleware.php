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

        $auth_key = $header_item['auth-key'][0];

        //check if access token is valid
        $access_auth = Access_Token::where('access_token', $auth_key)->first();

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

        $message_code = 0;

        if($key->expired_date < $now)
        {
            $delete_token = Access_Token::where('access_token', $key->access_token)->delete();

            if($delete_token > 0)
            {
                $message_code = 401;
            }
            else
            {
                $message_code = 500;
            }
        }
        else if(($key->expired_date - $now) <= (8 * 60 * 60))
        {
            $expired = $key->expired_date + (24 * 60 * 60);

            $update_token = Access_Token::where('access_token', $key->access_token)->first();

            $update_token->expired_date = $expired;

            $update_token->save();

            if(empty($update_token))
            {
                $message_code = 500;
            }
        }

        return $message_code;
    }
}
