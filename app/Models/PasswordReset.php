<?php

namespace App\Models;

use common\integration\Override\Model\CustomBaseModel as Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class PasswordReset extends Model
{
    protected $table = "password_resets";

    public function insertData($input)
    {
        $this->deleteData($input['email'], $input['user_type']);

        return self::query()->insert([
            "email" => $input['email'],
            "user_type" => $input['user_type'],
            "token" => Hash::make($input['token']),
            "created_at" => Carbon::now()
        ]);
    }

    public function deleteData ($email, $user_type)
    {
        return self::query()
            ->where("email", $email)
            ->where("user_type", $user_type)
            ->delete();
    }

    public function validateToken ($data)
    {
        $token_data = self::query()
            ->where("email", $data['email'])
            ->where("user_type", $data['user_type'])
            ->where('created_at', '>', Carbon::now()->subMinutes(config('auth.passwords.users.expire')))
            ->first();

        return !empty($token_data) ? Hash::check($data['token'], $token_data->token) : false;
    }

    public function revokeToken ($data)
    {
        return self::query()
            ->where("email", $data['email'])
            ->where("user_type", $data['user_type'])
            ->delete();
    }
}
