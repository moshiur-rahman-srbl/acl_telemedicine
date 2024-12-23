<?php


namespace common\integration\Utility\Sftp;


interface SftpConfigurationInterface
{

    public function getHost();

    public function getPort();

    public function getUsername();

    public function getPassword();

    public function getRootPath();


}