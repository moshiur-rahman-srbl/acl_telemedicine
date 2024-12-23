<?php

namespace common\integration\Models;

use common\integration\Override\Model\CustomBaseModel as Model;

class RandomCustomerIdSetting extends Model
{
    public $timestamps = false;

    public function findById($id)
    {
        return self::query()
            ->where('id', $id)
            ->first();
    }

    public function updateData(array $data, $id)
    {
        return self::query()
            ->where('id', $id)
            ->update($data);
    }
}
