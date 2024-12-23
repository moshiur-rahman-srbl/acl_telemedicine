<?php


namespace App\Http\Controllers\Traits;


use App\Models\ErrorMapping;

trait ErrorMappingTrait
{
    public function getMappedErrorMessage($bankId,$errorCode,$originalErrorMessage,$language){
         $errorMappingsObj = '';

         $errorMappingsObj = (new ErrorMapping())->findErrorMappings($bankId,$errorCode);

         if(!empty($errorMappingsObj)){
             if($language = 'en' && !empty($errorMappingsObj->message_en)){
                 $originalErrorMessage = $errorMappingsObj->message_en;
             }else if($language = 'tr' && !empty($errorMappingsObj->message_tr)){
                 $originalErrorMessage = $errorMappingsObj->message_tr;
             }
         }

         return $originalErrorMessage;
    }
}