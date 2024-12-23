<?php

namespace App\Http\Controllers\Traits;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait FileUploadTrait
{
    public function makeDir($path)
    {
        $storagePath = \common\integration\Utility\File::getStoragePath();
        if (!File::exists($storagePath . "/" . $path)) {
            $dir = File::makeDirectory($storagePath . "/" . $path, $mode = 0777, true, true);
        }
    }

    public function uploadFile($file, $path, $need = true, $extra_param = [])
    {
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('max_input_time', 500);
        ini_set('max_execution_time', 500);

        if ($need) {
            $this->makeDir($path);
        }
        if(!empty($extra_param) && !empty($extra_param['file_name'])){
            $file_path = $path. '/'. $extra_param['file_name'];
            $path = Storage::put($file_path, $file);
            $path = $file_path;
        }else{
            $path = Storage::put($path, $file);
        }
        return $path;
    }

    public function deleteFile($file)
    {
        if (! empty($file)) {
            Storage::delete($file);
        }
        return true;
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
