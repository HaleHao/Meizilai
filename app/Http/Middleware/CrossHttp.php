<?php

namespace App\Http\Middleware;

use Closure;

class CrossHttp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
//    public function handle($request, Closure $next)
//    {
//        $response = $next($request);
//        $response->header('Access-Control-Allow-Origin', '*');
//        $response->header('Access-Control-Expose-Headers','*');
//        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, Access-Token,X-Access-Token,access-token');
//        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
//        $response->header('Access-Control-Allow-Credentials', 'true');
//        $response->header('Access-Control-Request-Headers','Access-Token,access-token,x-access-token');
//        $response->header('Access-Token', '*');
//        $request->header('Access-Token','*');
//        return $response;
//    }
    public function handle($request, Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Content-Type,Access-Token");
        header("Access-Control-Expose-Headers: *");

        return $next($request);
    }
}
