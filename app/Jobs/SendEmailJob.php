<?php

namespace App\Jobs;

use common\integration\CashInOut;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendMail;
use common\integration\BrandConfiguration;
use common\integration\SoapMailer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data, $email_subject,$from, $attachment, $physical_template,$to, $unlink_attachment;
    public $tries = 3;
    /**
     * Create a new job instance.
     * hhh
     * @return void
     */
    public function __construct($data, $email_subject,$from, $attachment, $physical_template,$to, $unlink_attachment = false)
    {
        $this->data = $data;
        $this->email_subject = $email_subject;
        $this->from = $from;
        $this->attachment = $attachment;
        $this->physical_template = $physical_template;
        $this->to = $to;
        $this->unlink_attachment = $unlink_attachment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      //commit test1
      if(BrandConfiguration::sendMailViaWsdl()){

            $body = View::make('email.'.$this->physical_template, ['data' => $this->data])->render();

            (new SoapMailer())->sendEmail($this->to,$this->from,$this->email_subject,$body,$this->attachment);

        }else{

            $is_sent = Mail::to($this->to)->send(new SendMail($this->data, $this->email_subject,$this->from, $this->attachment, $this->physical_template));

            if ($is_sent) {
                if ($this->unlink_attachment) {
                    File::delete($this->attachment);
                }
            }
        }
    }
}
