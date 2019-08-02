<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;


class AccessTokenToUser
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
        $token = $request->header('Access-Token');
        $user_id = Cache::get($token);
        if($user_id){
            $arr = [
                'user_id' => $user_id
            ];
            $request->merge($arr);

        }
        return $next($request);
    }

}