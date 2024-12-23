<?php
namespace App\Http\Controllers\Traits;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    public function makeDir($path, $disk = 'public')
    {
        $storagePath = \common\integration\Utility\File::getStoragePath($disk);

        if (!File::exists($storagePath . "/" . $path)) {
            $dir = File::makeDirectory($storagePath . "/" . $path, $mode = 0777, true, true);
        }
    }

    public function uploadFile($file, $path, $fileName = '', $create_dir=true)
    {
        $path = \common\integration\Utility\File::manipulateBrandResourceDynamicPath($path);
        if($create_dir) {
            $this->makeDir($path);
        }
        if (!empty($fileName)){
            $path = Storage::putFileAs($path, $file, $fileName);
        }else{
            $path = Storage::put($path, $file);
        }

        return $path;
    }

    public function getExtension($fileName)
    {
        return trim(explode('.', $fileName)[1]);
    }

    public function deleteFile($file, $is_realpath = false)
    {
        if($is_realpath){
            @unlink($file);
        }else{
            if (! empty($file)) {
                Storage::delete($file);
            }
        }

        return true;
    }

    /**
     * Move file from one location to another.
     *
     * @param $from - old file path
     * @param $to - new file path
     * @return mixed
     */
    public function moveFile($from, $to)
    {
        return Storage::move($from, $to);
    }

    public function moveResourceFile($path, $directory, $file, $need=true){
        try {
            if ($need) {
                $this->makeDir($directory);
            }
            $path = Storage::move($path, $file);
            if($path){
                return $file;
            }
        } catch (\Throwable $ex){
            //dd($ex->getMessage());
        }
        return false;
    }
}
