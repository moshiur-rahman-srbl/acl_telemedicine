<?php


namespace common\integration\Utility\Sftp;


use common\integration\Utility\Exception;
use Illuminate\Support\Facades\Storage;


class Sftp
{
    private $connection;

    private $session;

    private $is_successful;


    public function __construct(SftpConfigurationInterface $sftpConfiguration)
    {
        $this->resetStatus();

        $this->connect($sftpConfiguration);

    }

    private function connect(SftpConfigurationInterface $sftpConfiguration)
    {

        if (!($this->connection = ssh2_connect($sftpConfiguration->getHost(), $sftpConfiguration->getPort()))) {
            throw new \Exception('Cannot connect to server');
        }


        if(!ssh2_auth_password($this->connection, $sftpConfiguration->getUsername(), $sftpConfiguration->getPassword())){
            throw new \Exception('Authentication failed');
        }


        $this->session = ssh2_sftp($this->connection);

/*
        if(!($this->connection = ssh2_sftp($this->connection))){
            throw new \Exception('Sftp subsystem initialization failed');
        }*/
    }



    public function exec($cmd)
    {

        if (!($stream = ssh2_exec($this->connection, $cmd))) {
            throw new \Exception('SSH command failed');
        }
        stream_set_blocking($stream, true);
        $data = "";
        while ($buf = fread($stream, 4096)) {
            $data .= $buf;
        }
        fclose($stream);
        return $data;
    }

//   disk can be public/local/... here public is brandResource
    public function download($remote_file_path, $source_file_path, $encoding = null, $disk = null)
    {
        try {
            $stream = @fopen("ssh2.sftp://" . $this->session . $remote_file_path, 'r');
            if ($encoding) {
                stream_filter_append($stream, "convert.iconv.$encoding.utf-8");
            }
            if (!$stream) {
                throw new \Exception("Stream failed");
            } else {
                if (!empty($disk)){
                    Storage::disk($disk)->put($source_file_path, $stream);
                }else{
                    file_put_contents($source_file_path, $stream);
                }
                $this->is_successful = true;
                
            }
        }catch (\Throwable $throwable){
            Exception::log($throwable);
        }

    }

    
  
    public function upload($remote_file_path, $source_file_path)
    {
        
        try {
            $sftpStream = @fopen('ssh2.sftp://' . $this->session . $remote_file_path, 'w');
            if (!$sftpStream) {
                throw new \Exception("Could not open remote file: $remote_file_path");
            }
            $data_to_send = @file_get_contents($source_file_path);

            if ($data_to_send === false) {
                throw new \Exception("Could not open local file: $source_file_path.");
            }

            if (@fwrite($sftpStream, $data_to_send) === false) {
                throw new \Exception("Could not send data from file: $source_file_path.");
            }

            fclose($sftpStream);
            $this->is_successful = true;
        }catch (\Throwable $throwable){
            Exception::log($throwable);
        }
        //return ssh2_scp_send($this->session, $source_file_path, $remote_file_path, 0644);
    }

    public function isFileExist($remote_file_path)
    {
        try {
            $stream = @fopen("ssh2.sftp://" . $this->session . $remote_file_path, 'r');
            if (!$stream) {
                return false;
            } else {
                return true;
            }
        }catch (\Throwable $throwable){
            Exception::log($throwable);
        }
    }


    public function read($remote_file_path)
    {
        try {
            $stream = fopen("ssh2.sftp://" . $this->session . $remote_file_path, 'r');
            if (!empty($stream)) {
                return stream_get_contents($stream);
            } else {
                return null;
            }
        }catch (\Throwable $throwable){
            Exception::log($throwable);
        }
    }


    public function disconnect()
    {
        $this->exec('echo "EXITING" && exit;');
        $this->connection = null;
    }


    public function __destruct()
    {
        // $this->disconnect();
    }

    public function isSuccessful()
    {
        return $this->is_successful;
    }

    public function resetStatus(bool $status = false): bool
    {
        return $this->is_successful = $status;
    }
}