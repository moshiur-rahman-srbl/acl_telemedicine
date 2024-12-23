<?php

namespace common\integration\Override;

use common\integration\Traits\CustomConnectionTrait;
use Illuminate\Database\SQLiteConnection;

class CustomSQLiteConnection extends SQLiteConnection
{
    use CustomConnectionTrait;
}