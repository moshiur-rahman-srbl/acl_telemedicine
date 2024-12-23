<?php


namespace common\integration\Utility\Sftp;


abstract class AbstractSftpConfiguration implements SftpConfigurationInterface
{
    protected $host;


    protected $port;


    protected $username;


    protected $password;


    protected $root_path;


    public function getHost()
    {
        return $this->host;

    }

    public function getPort()
    {
        return $this->port;

    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }


    public function getRootPath()
    {
        return $this->root_path;
    }

}