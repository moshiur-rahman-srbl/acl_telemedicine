<?php

namespace common\integration;

use Illuminate\Contracts\Translation\Loader;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader as LaravelFileLoader;
use RuntimeException;

class FileLoader extends LaravelFileLoader
{
     protected function loadJsonPaths($locale)
     {
          $outputs = [];
          return collect(array_merge($this->jsonPaths, [$this->path]))
               ->reduce(function ($output, $path) use ($locale, $outputs) {

                    if(\is_array($path)){
                         foreach($path as $key => $path){
                              if ($this->files->exists($full = "{$path}/{$locale}.json")) {
                                   $decoded = json_decode($this->files->get($full), true);
                                   if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                                        throw new RuntimeException("Translation file [{$full}] contains an invalid JSON structure.");
                                   }
                                   $output[] =  $decoded;
                              }
                         }

                         if(sizeof($output) > 0 && is_array($output)){
                              foreach($output as $key => $value){
                                   if(!empty($value)){
                                        $outputs = $value + $outputs;
                                   }
                              }
                         }

                    }else{
                         if ($this->files->exists($full = "{$path}/{$locale}.json")) {
                              $decoded = json_decode($this->files->get($full), true);
          
                              if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                                   throw new RuntimeException("Translation file [{$full}] contains an invalid JSON structure.");
                              }
          
                              $outputs = array_merge($output, $decoded);
                         }
                    }
                    return $outputs;
          }, []);
     }

     protected function loadPath($path, $locale, $group)
     {
          $file_contents = [];
          $values = [];
          if(is_array($path)){
               foreach($path as $key => $path){
                    if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
                         $file_contents[] = $this->files->getRequire($full);
                    }
               }
          }else{
               if($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
                    $file_contents = $this->files->getRequire($full);
               }
          }

          if(sizeof($file_contents) > 0 && is_array($file_contents)){
               foreach($file_contents as $key => $value){
                    if(!empty($value)){
                         $values = $value + $values  ;
                    }
               }
          }

          return $values;
     }


}
