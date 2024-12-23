<?php

namespace App\Jobs;

use App\Http\Controllers\Traits\CommonLogTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use common\integration\SendSmsByProvider;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CommonLogTrait;

    private $phones,$header,$message;

    public $tries = 3;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($header, $message, $phones)
    {
        $this->phones = $phones;
        $this->header = $header;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $this->sendSMS($this->header, $this->message, $this->phones);
    }
    
    
    private function sendSMS($header, $message, $phones)
    {
        $phones = trim($phones);
        // $phones = "+37062224012";
        //$message = "test by Rifat";
        $this->fileWrite("otp.txt", $message, config('app.IS_OTP_FILE_WRITE_ENABLE'));
        
        $username = config('app.OTP_API_KEY');
        $password = config('app.OTP_API_SECRET');
        $otp_gateway_name = config('app.OTP_GATEWAY_NAME');
        $otp_from_name = config('app.OTP_FROM_NAME');

        $is_otp_enable = config('app.is_otp_enable');
        
        if(!empty($is_otp_enable)){
            (new SendSmsByProvider($username, $password, $header, $message, $phones, $otp_from_name, $otp_gateway_name))
                ->sendSms();
        }
    }

    
}
