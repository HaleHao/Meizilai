<?php

namespace App\Http\Middleware;

use Closure;
use Overtrue\Socialite\User as SocialiteUser;

class MockUser
{
    public function handle($request, Closure $next)
    {

        $user = new SocialiteUser([
            'id' => '12345',//openid
            'name' => 'mock',
            'nickname' => 'mock user',
            'avatar' => '',
            'email' => null,
            'original' => [],
            'provider' => 'WeChat',
            'gender' => 1
        ]);
        session(['wechat.oauth_user.default' => $user]);
        return $next($request);
    }
}