<?php

namespace common\integration\Override;


use common\integration\Traits\CustomConnectionTrait;
use Illuminate\Database\MySqlConnection;

class CustomMySqlConnection extends MySqlConnection
{
    use CustomConnectionTrait;
}