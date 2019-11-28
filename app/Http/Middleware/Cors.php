<?php

namespace App\Http\Middleware;


use Closure;

class Cors {
    public function handle($request, Closure $next)
    {   
        $response = $next($request);
            $response->headers->set('Access-Control-Allow-Origin','*');
            $response->headers->set('Access-Control-Allow-Methods','GET,POST,PUT,DELETE,OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers','Origin, Content-Type, X-Auth-Token, X-Auth-Token, X-CSRF-TOKEN, Authorization, X-Requested-With, Accept');

        return $response;

    }
}