<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class OauthAccessToken extends Model
{
    protected $table = 'oauth_access_tokens';
    const REVOKED = 1;

    public function revokAccessTokenByUserId ($user_id)
    {
        return self::query()->where('user_id', $user_id)->update(['revoked' => self::REVOKED]);
    }
}
