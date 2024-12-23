<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Traits\ApiResponseTrait;
use Closure;
use App\Http\Controllers\Traits\PermissionUpdateTreait;
use Session;
use Config;
class Permission
{
    use PermissionUpdateTreait, ApiResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $isPermission = false;
        $user_permissions = Session::get('user_permission');
        $request_action = $request->route()->getActionMethod();
        preg_match('/([a-z]*)@/i', $request->route()->getActionName(), $matches);
        $controllerName = $matches[1];
        //$controllerName = strtolower(str_replace("Controller", "", $controllerName));
        $pages = $this->getPages($controllerName);
//        echo $request_action;
//        dd($pages);exit;

        $current_permission_version = Session::get('permission_version');
        $updated_permission_version = auth()->user()->permission_version;
        $user_id = auth()->user()->id;
        if($updated_permission_version > $current_permission_version){
            $this->getPermissionList($user_id);
        }

        $user_permissions = Session::get('user_permission');

        if (array_key_exists($request_action, $pages)) {
            $action_page_id = $pages[$request_action];
            //echo $action_page_id;exit;
            foreach ($user_permissions as $permission) {
                if ($permission->page_id == $action_page_id) {
                    $isPermission  = true;
                    break;
                }
            }
        }


        //dd($user_permissions);exit

        if($isPermission){
            return $next($request);
        }

        if($request->wantsJson()){
            return $this->sendApiResponse(__("You dont have permission of this page"), [], 403);
        }

       // return $next($request);
       flash(__("You dont have permission of this page"), "danger");
       return redirect(route('home'));

    }
}
