<?php

namespace common\integration\Override;

use common\integration\Traits\CustomConnectionTrait;
use Illuminate\Database\SqlServerConnection;

class CustomSqlServerConnection extends SqlServerConnection
{
    use CustomConnectionTrait;
}