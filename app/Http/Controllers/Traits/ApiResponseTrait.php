<?php
namespace App\Http\Controllers\Traits;

use App\User;
use common\integration\GlobalFunction;
use common\integration\ManageLogging;

trait ApiResponseTrait
{
    public function sendApiResponse($description, $data, $statusCode, $standard_api = false, $is_cashout = false, $extraData = []) {

        if (empty($data) || ! is_array($data)) {
            $data = [];
        }

        $data = $this->nullRemovalMethod(json_decode(json_encode($data), true), null, '');

       if($standard_api){
          $response = [
            'status_code' => $statusCode,
            'status_description' => $description,
            'data'=>$data
          ];
       }else {
          $response = [
            'statuscode' => $statusCode,
            'description' => $description,
            'data' => $data
          ];

          if ($is_cashout) {
              $response['status_code'] = $statusCode;
              $response['status_description'] = $description;
          }
       }

        //dd($response);
        if (!empty($errorMessages)) {
            $response['data'] = $data;
        }

        if (!empty($extraData)) {
            $response = array_merge($extraData, $response);
        }

        return response()->json($response, 200);
    }


    public function sendSuccessResponse($result, $message,$statusCode)
    {
    	$response = [
            'success' => true,
            'statuscode' => $statusCode,
            'data'    => $result,
            'message' => $message,
        ];


        return response()->json($response, 200);
    }

    public function sendApiServiceResponse ($status_code, $status_description, $data,
                                            $http_code = 200, $shouldBeLogged = false,
                                            $action = null):object
    {
        $response_data['action'] = $action;
        $response_data['status_code'] = $status_code;
        $response_data['status_description'] = $status_description;

        if(!empty($data)) {
            $response_data['data'] = $data;
        }

        if($shouldBeLogged){
            (new ManageLogging())->createLog($response_data);
        }

        unset($response_data['action']);

        return response()->json($response_data, $http_code, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_SLASHES);

    }

    public function nullRemovalMethod ($maybe_array, $replace_from, $replace_to)
    {
        if (!empty($maybe_array)) {
            if (is_array($maybe_array)) {
                foreach ($maybe_array as $key => $value) {
                    $maybe_array[$key] = $this->nullRemovalMethod($value, $replace_from, $replace_to);
                }
            }
        }

        return is_null($maybe_array) ? '' : $maybe_array;
    }


}
