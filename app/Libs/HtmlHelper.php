<?php
/**
 * Created by PhpStorm.
 * User: Tushi
 * Date: 8/23/2019
 * Time: 4:06 PM
 */

namespace App\Libs;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class HtmlHelper
{

    public static function disPlayConversation($ticket)
    {
        $time = DateHelper::format(4, $ticket->updated_at);
        $date = DateHelper::format(5, $ticket->updated_at);
        $supportedExt = ['jpeg','jpg','png','txt','pdf','xls'];
        $ext = pathinfo($ticket->attachment, PATHINFO_EXTENSION);
        $fileName = pathinfo($ticket->attachment, PATHINFO_FILENAME);
        $downloadLink = route(Config::get('constants.defines.APP_SUPPORT_DOWNLOAD'),$ticket->id);
        $attachmentFile = Storage::url("attachment.png");
        $html = "";
        $attachmentPath = "";
        if(in_array($ext, $supportedExt)) {
            $attachmentPath .= "<a href=" . $downloadLink;
            $attachmentPath .= sprintf(" ><img src=%s alt='img' width ='20' height='20' class='img'/>", $attachmentFile);
            $attachmentPath .= "</a>";
        }
        if ($ticket->parent_id == 0) {
            $html .= "<div class='chat1'>";
            $html .= "<span class=\"user-icon\">";
            if (!empty($ticket->avatar) && file_exists(\Illuminate\Support\Facades\Storage::url($ticket->avatar)))
                $html .= "<img src =".\Illuminate\Support\Facades\Storage::url($ticket->avatar)." alt = \"img\">";
            else {
                $html .= "</span>";
            }
            if (!empty($ticket->message)) {
                $html .= "<span class=\"user-comment\">";
                $html .= $ticket->message;
                $html .= "</span>";
                $html .= "<p style = \"width:100%; float:left; margin:0;text-align: left\">";
                $html .= "<small>" . $time . " | " . $date . "</small></p>";
                $html .= " <p style =\"width:100%; float:left; margin:0;text-align: left\" >";
                $html .= $attachmentPath;
                $html .= "</p></div>";
            }
        } else {
            $html .= "<div class=\"chat2\">";
            if (!empty($ticket->message)) {
                $html .= "<span class=\"user-comment\">";
                $html .= $ticket->message;
                $html .= "</span>";
                $html .= "<p style = \"width:100%; float:left; margin:0;text-align: left\">";
                $html .= "<small>" . $time . " | " . $date . "</small></p>";
                $html .= " <p style =\"width:100%; float:left; margin:0;text-align: right\" >";
                $html .= $attachmentPath;
                $html .= "</p></div>";
            }

        }
        return $html;
    }


}