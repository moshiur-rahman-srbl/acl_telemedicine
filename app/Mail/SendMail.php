<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;


class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $xdata;
    public $xsubject;
    public $xfrom;
    public $xattachment;
    public $xtemplate;
    public $xbcc;
    public $xcc;
    public $xrendered;

    public function __construct($data, $subject, $from, $attachment, $template, $is_rendered_view=false)
    {
        $this->xdata = $data;
        $this->xsubject = $subject;
        $this->xfrom = $from;
        $this->xattachment = $attachment;
        $this->xtemplate = $template;
        $this->xrendered = $is_rendered_view;
        $this->xbcc = $data['bcc_email'] ?? '';
        $this->xcc = $data['cc_email'] ?? '';
    }

    public function build()
    {
        $xemail = $this->subject($this->xsubject)->from($this->xfrom, config('constants.defines.MAIL_FROM_NAME'));

        if (!empty($this->xattachment)) {
            $xemail->attach($this->xattachment);
        }
        if (!empty($this->xbcc)) {
            $xemail->bcc($this->xbcc);
        }
        if (!empty($this->xcc)) {
            $xemail->cc($this->xcc);
        }
        if ($this->xrendered) {
            $xemail->html($this->xdata);
        } else {
            $xemail->markdown('email.'.$this->xtemplate)->with(['data' => $this->xdata]);
        }

        return $xemail;
    }
}
