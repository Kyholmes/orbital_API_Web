<?php

namespace App\Http\Middleware;

use Closure;
use Response;

class ApiAuthMiddleware
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

        $api_key = $header_item['api-key'][0];

        //check if api token is valid
        $api_auth = "08006c47-d0b9-4990-adb1-7d76610a4536";

        if(empty($api_auth))
        {
            return Response::json([
                'error' => [
                    'code' => 'UNAUTH',
                    'http_code' => 401,
                    'message' => 'API key invalid'
                ]], 401);
        }

        return $next($request);
    }
}
