<?php

namespace common\integration\Override;


use common\integration\Traits\CustomConnectionTrait;
use Illuminate\Database\PostgresConnection;

class CustomPostgresConnection extends PostgresConnection
{
    use CustomConnectionTrait;
}