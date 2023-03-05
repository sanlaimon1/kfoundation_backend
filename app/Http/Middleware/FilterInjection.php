<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterInjection
{
    /**
     * file parameters to avoid SQL injection
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        if(!empty($input))
        {
             foreach( $input as $key=>$value ) {
                 if( $key==='_token' or $key==='_method')
                     continue;
                 else
                 {
                     $value = strtolower( $value );  //先变成小写字母
                     //过滤 select |alter|rename|modify|passwd|convert|char|response|root|into
                     if(preg_match("/select|update|insert|delete|and|where|union|join|create|drop|like|eval|open|sysopen|system|passwd|alter|char|\"|\'|\?/", $value) )
                     {
                         $arr = ['code'=>-30, 'message'=> $key . '里包含非法参数' . $value];
                         return response()->json( $arr );
                     }    
                        
                 }
                
             }
            
        }
        
        return $next($request);
    }
}
