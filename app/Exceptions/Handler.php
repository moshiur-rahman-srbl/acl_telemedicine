<?php

namespace App\Exceptions;

use App\Http\Controllers\Traits\ApiResponseTrait;
use App\Http\Controllers\Traits\CommonLogTrait;
use common\integration\ManageLogging;
use common\integration\Utility\Exception;
use Illuminate\Auth\AuthenticationException;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use UnexpectedValueException;

class Handler extends ExceptionHandler
{
    use CommonLogTrait, ApiResponseTrait;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        $exception =(new Exception())->handle($exception);
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception) {

        if($request->wantsJson() || $request->isJson()){
            $message = 'Something Went Wrong!';

            if ($exception instanceof AuthenticationException){
                $message = $exception->getMessage();
            }

            return $this->sendApiResponse($message, [], $exception->getCode());
        }
        $exception =(new Exception())->handle($exception);

        if ($exception instanceof TokenMismatchException) {
            return redirect()->route('login');
        }elseif ($exception instanceof UnexpectedValueException){
            $logData['Action'] = 'The Response content Exception';
            $logData['url'] = $request->url();
            $logData['message'] = $exception->getMessage();
            $logData['contents'] = $request->all();
            $this->createLog($this->_getCommonLogData($logData));

        } else{

            if ($this->isHttpException($exception)) {
                $statusCode = $exception->getStatusCode();
                $data['statuscode'] = $statusCode;
//                $data['message'] = $exception->getMessage();

                $data['message'] = match ($statusCode){
                        config('apiconstants.API_VERIFICATION_FAILED') => $exception->getMessage(),
                        default => "System Error. Please contact Support."
                    };

                return response()->view('errorpages.error',$data,$statusCode);
            }

        }

        return parent::render($request, $exception);
    }
}
