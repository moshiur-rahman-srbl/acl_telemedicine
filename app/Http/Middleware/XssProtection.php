<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\CommonLogTrait;
use Closure;
use Symfony\Component\Routing\Route;

class XssProtection
{
    use CommonLogTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ignorablePaths = ['settings/edit', config('constants.defines.ADMIN_URL_SLUG').'/merchantAgreement',
                  config('constants.defines.ADMIN_URL_SLUG').'/static-content/add',
                  config('constants.defines.ADMIN_URL_SLUG').'/static-content/edit/'.last(request()->segments())
        ];
        $uri = $request->path();
//        if ($request->isMethod('post')){

            $data = $request->all();
            $newData = [];
            if (!empty($data)){
                foreach ($data as $key=>$value){

                    if (is_array($value)){
                        $encoded_value = json_encode($value);
                        $striped_value = $this->getXssProtectedvalue($encoded_value);
                        $decoded_value = json_decode($striped_value, true);
                        $newData[$key] = $decoded_value;
                    }else{
                        if (!in_array($uri,$ignorablePaths)){
                            $newData[$key] = $this->getXssProtectedvalue($value);
                        }

                    }

                }
                $request->merge($newData);
            }
//        }

        return $next($request);
    }
}
