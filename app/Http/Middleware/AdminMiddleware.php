<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        $module = Auth::guard()->user()->type;
            // dd($module);
        if ( $module != explode("/", $request->route()->getPrefix())[1] ) {
            // dd($module);
            return redirect( $module . "/dashboard" );
        }

        return $next($request);
        // if(Auth::user()->type == 'admin')
        // {
        //     return $next($request);
        // }

        // else
        // {
        //     return redirect('/dashboard');
        // }

        // return $next($request);
    }
}
