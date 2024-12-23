<?php

namespace common\integration\Utility\Sftp;


class GeneralSftpConfiguration extends  AbstractSftpConfiguration
{

    public function __construct( $host, $username, $password, $port, $root_path)
    {
        $this->host = $host;

        $this->username = $username;

        $this->password =$password;

        $this->port = $port;

        $this->root_path = $root_path;
    }

}