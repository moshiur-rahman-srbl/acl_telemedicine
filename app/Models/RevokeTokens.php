<?php

namespace App\Models;

use common\integration\GlobalFunction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class RevokeTokens
{

    public function logoutPastLogin($userId){

        $tokens = DB::table('oauth_access_tokens')->get()
                                                  ->where('user_id', $userId)
                                                  ->where('revoked', false);
        $token_ids = [];
        $sql = "";
        foreach($tokens as $token){
            //$token_ids[] = $token->id;
            $sql .= "update oauth_refresh_tokens set revoked = 1 where access_token_id = '".$token->id."';";
        }
        if(!empty($sql)){
            DB::unprepared($sql);
//            DB::table('oauth_refresh_tokens')
//                ->whereIn('access_token_id',$token_ids)
//                ->update([
//                    'revoked' => true
//                ]);

        }
        DB::table('oauth_access_tokens')
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->update([
                'revoked' => true
            ]);

        $previous_session = DB::table('users')->where('id', $userId)->value('session_id');
        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
        }

        GlobalFunction::setLogoutOtherDeviceWarning($previous_session);
    }
}
