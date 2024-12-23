<?php
/**
 * User: md Yeasin
 * Date: 8/22/2019
 * Time: 4:06 PM
 * Licence: MIT
 *
 * description:
 *  this sample html helper for build dynamic html
 */

namespace App\Utils;


use App\User;
use App\Models\TransactionState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use common\integration\GlobalFunction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class Html
{

    /**
     * Build dynamic chat box message and return html string
     *
     * @param $ticket
     * @return string
     */
    public static function displayConversationMessage($ticket)
    {
        /**
         * defined initial value
         * for date formatting use @see Date class
         */
        $time = Date::format(4, $ticket->created_at);
        $date = Date::format(5, $ticket->created_at);
        //$supportedExt = ['jpeg', 'jpg', 'png', 'txt', 'pdf', 'xls', 'doc', 'ppt', 'xlsx', 'docx', 'pptx'];
        $supportedExt = ['png', 'jpg', 'jpeg', 'webp', 'tif', 'tiff', 'pdf'];

        /*
         * get the file extension
         */
        $ext = pathinfo($ticket->attachment, PATHINFO_EXTENSION);
        /*
         * get the file base name
         */
        $fileName = pathinfo($ticket->attachment, PATHINFO_FILENAME);
        /*
         * download route path for download request
         */
        $downloadLink = url(config("constants.defines.ADMIN_URL_SLUG").'/support/conversation/download/'.$ticket->id.'/'.$ticket->ticket_id);

        /*
         * defined for generate html
         */
        $html = "";

        /*
         * defined for build dynamic attachment path
         */
        $attachmentPath = "";
        /*
         * check file extension is supported or not
         * if supported extension then build attachment path Or
         * leave the blank
         */
        if (in_array($ext, $supportedExt)) {
            $attachmentPath .= "<a style=\"background: #fff; color: #03a9f5;\" href=" . $downloadLink;
//            $attachmentPath .= sprintf(" ><img src=%s alt='img' width ='20' height='20' class='img'/>", $attachmentFile);
            $attachmentPath .= "><i class=\"fa fa-paperclip\"></i> Attachment";
            $attachmentPath .= "</a>";
        }

        /**
         * check and build customer message that he/she send to the support team
         *
         */
        if ($ticket->messagefrom == "CUSTOMER") {
            if (!empty($ticket->message)) {
                $html .= "<div class='chat1'>";
                $html .= "<span class=\"user-icon\">";

                if (!empty(User::avatar($ticket->user_id)) && Storage::exists(User::avatar($ticket->user_id)))
                    $html .= "<img src =" . \Illuminate\Support\Facades\Storage::url(User::avatar($ticket->user_id)) . " alt = \"img\"/></span>";
                else {
                    $html .= "<img src =" . GlobalFunction::getProfilePic($ticket->gender, User::avatar($ticket->user_id)) . " alt = \"img\"/></span>";
                }

                /*
                 * check message blank or Not if not blank
                 * build message html
                 */
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
            /*
             * build only receiver(like support team) message that he/she send to customer
             */
            $responding_name = '';
            if (isset($ticket->user->name) && !empty($ticket->user->name)) {
                $user_name = explode(' ', $ticket->user->name);
                $responding_name = $user_name[0];
                if (isset($user_name[1])) {
                    $responding_name .= ' ' . substr($user_name[1], 0, 1);
                }
                $responding_name .= '****';
            }

            if (!empty($ticket->message)) {
                $html .= "<div class=\"chat2\">";
                $html .= "<p style = \"width:100%; float:left; margin:0;text-align: right\">".$responding_name."</p>";
                $html .= "<span class=\"user-comment\">";
                $html .= $ticket->message;
                $html .= "</span>";
                $html .= "<p style = \"width:100%; float:left; margin:0;text-align: right\">";
                $html .= "<small>" . $time . " | " . $date . "</small></p>";
                $html .= " <p style =\"width:100%; float:left; margin:0;text-align: right\" >";
                $html .= $attachmentPath;
                $html .= "</p></div>";
            }

        }
        return $html;
    }


    /**
     * generate dynamic button based on sale transaction id
     *
     * @param $status
     * @param $trans_id
     * @return string
     */
    public static function getStatusButton($status, $trans_id){

        $html = "<td><button class=\"details-btn btn btn-outline-secondary rounded btn-sm font13\"
                                                   data-trans_id=\"{{$trans_id}}\">{{__(\"Cancel\")}}</button></td>";
        switch ($status){
            case TransactionState::CHARGE_BACK_APPROVED:
            case TransactionState::CHARGE_BACK_REJECTED:
            case TransactionState::CHARGE_BACK_REQUESTED:
                return $html;
            default:
                return "";
        }
    }

    /**
     * buildForm function only access able function this htmlHelper class.because
     * all others function are private that not access able.if you use the function from
     * view you must be call this function.this function call all other necessary function
     * for build dynamic html form.
     * ======================== Remember ==============================
     * function parameter must be a @see Collection instance otherways error accur
     *
     * @param Collection $fields
     * @return string
     */
    public static function buildForm(Collection $fields)
    {
        $html = '';
        if ($fields->count() > 0) {
            foreach ($fields->all() as $key => $field) {
                if (isset($field['wrapper']) && count($field['wrapper'])) {
                    $html .= self::generateHtml(($field['wrapper']));
                } else {
                    $html .= "<div style='margin: 0 auto; color: red;'><p>Your Form Format is invalid. please check Form Format form Form dir</p></div>";
                }
            };
        } else {
            $html .= "<div style='margin: 0 auto; color: red;'><p>Your Form Format is invalid. please check Form Format</p></div>";
        }

        // dd($html);
        return $html;
    }

    /**
     * starting point for build html wrapper and form group element
     *
     * @param $field
     * @return string
     */
    private static function generateHtml($field)
    {
        $html = "";
        // dd($field['tag']);
        switch (trim($field['tag'])) {
            case 'div':
                $html .= method_exists(self::class, "startRow") ? self::startRow($field) : null;
                break;
            default:
                break;
        }

        /**
         * check have any form fields.if yes then next process
         */
        if (isset($field['fields']) && count($field) > 0) {
            $html .= self::formGroup($field['fields']);
        }

        //end inner div
        $html .= self::endRow();
        //end parent div
        $html .= self::endRow();

        return $html;
    }

    /**
     * build wrapper element like div.
     *
     * @param array $field
     * @return string
     */
    private static function startRow($field = array())
    {
        /*
         * initialize
         */
        $html = "";
        $html .= "<div ";

        /**
         * check have any attribute for wrapper
         */
        if (isset($field['attr']) && !empty($field['attr'])) {
            $html .= self::getAttr($field['attr']);
        }
        $html .= ">";

        /**
         * also check have any nested wrapper
         */
        if (isset($field['innerWrapper']) && count($field['innerWrapper']) > 0) {
            $html .= self::startRow($field['innerWrapper']);
        }

        return $html;
    }


    /**
     * build form group element
     *
     * @param array $field
     * @return string
     */
    private static function formGroup($field = array())
    {
        $html = "";
        /**
         * first check any label defined Or not
         */
        if (isset($field['label']) && count($field['label']) > 0) {
            $html .= self::label($field['label']);
        }

        /**
         * then check have any input element Or not
         */
        if (isset($field['input']) && count($field['input']) > 0) {
            $html .= self::inputField($field['input']);
        }
        return $html;
    }

    /**
     * build attribute for any element
     *
     * @param array $attr
     * @param array $default
     * @return string
     */
    private static function getAttr($attr = array(), $default = array())
    {
        $localAttr = array();
        $attList = "";
        if (is_array($default) && !empty($default)) {
            $localAttr = $default;
        }
        if (is_array($attr) && !empty($attr)) {
            $localAttr = array_merge($localAttr, $attr);
        }
        foreach ($localAttr as $name => $value) {
            if (empty($value)) $attList .= $name;
            else
                $attList .= "{$name}=\"{$value}\" ";
        }

        return $attList;
    }

    /**
     * end the wrapper
     *
     * @return string
     */
    private static function endRow()
    {
        return "</div>";
    }

    /**
     * build individual input element
     *
     * @param array $field
     * @return null|string
     */
    private static function inputField($field = array())
    {
        $formField = '';
        switch (isset($field['type']) ? $field['type'] : 'invalid') {
            case 'startlegend':
                $formField .= method_exists(self::class, "startLegend") ? self::startLegend($field) : null;
                break;
            case 'endlegend':
                $formField .= method_exists(self::class, "endLegend") ? self::endLegend($field) : null;
                break;
            case 'label':
                $formField .= method_exists(self::class, "label") ? self::label($field) : null;
                break;
            case 'text':
                $formField .= method_exists(self::class, "textField") ? self::textField($field) : null;
                break;
            case 'textarea':
                $formField .= method_exists(self::class, "textAreaField") ? self::textAreaField($field) : null;
                break;
            case 'select':
                $formField .= method_exists(self::class, "selectField") ? self::selectField($field) : null;
                break;
            case 'checkbox':
                $formField .= method_exists(self::class, 'checkBoxField') ? self::checkBoxField($field) : null;
                break;
            case 'radio':
                $formField .= method_exists(self::class, 'radioField') ? self::radioField($field) : null;
                break;
            case 'email':
                $formField .= method_exists(self::class, 'emailField') ? self::emailField($field) : null;
                break;
            case 'file':
                $formField .= method_exists(self::class, 'fileField') ? self::fileField($field) : null;
                break;
            case 'password':
                $formField .= method_exists(self::class, 'passwordField') ? self::passwordField($field) : null;
                break;
            case 'number':
                $formField .= method_exists(self::class, 'numberField') ? self::numberField($field) : null;
                break;
            case 'date':
                $formField .= method_exists(self::class, 'dateField') ? self::dateField($field) : null;
                break;
            case 'hidden':
                $formField .= method_exists(self::class, 'hiddenField') ? self::hiddenField($field) : null;
                break;
            case 'submit':
                $formField .= method_exists(self::class, 'submitButton') ? self::submitButton($field) : null;
                break;
            case 'startrow':
                $formField .= method_exists(self::class, 'startRow') ? self::startRow() : null;
                break;
            case 'endrow':
                $formField .= method_exists(self::class, 'endRow') ? self::endRow() : null;
                break;
                break;
            default:
                return null;
        }

        return $formField;
    }

    /**
     * build label element
     *
     * @param array $field
     * @return string
     * @internal param $label
     */
    private static function label($field = array())
    {
        // dd($label);
        $html = "<label ";
        if (isset($field['name']) && !empty($field['name']))
            $html .= "for=\"" . $field['name'] . "\"";
        if (isset($field['attr']) && is_array($field['attr']) && count($field['attr']) > 0)
            $html .= self::getAttr($field['attr']);

        $html .= ">" . $field['name'] . "</label>";

        return $html;

    }

    /**
     * build legend element
     *
     * @param $field
     * @return string
     */
    private static function startLegend($field)
    {

        return "";
    }

    /**
     * end legend element
     *
     * @param $field
     * @return string
     */
    private static function endLegend($field)
    {
        return "";
    }

    /**
     * actual input builder function that call from all element function
     *
     * @param $field
     * @param array $languages
     * @return string
     */
    private static function input($field, $languages = array())
    {
        //dd($field);
        $html = "<input type=";
        if (is_array($field) && count($field) > 0) {
            $html .= isset($field['type']) ? strtolower(trim($field['type'])) : "text";
            $html .= " value=";
            if (isset($field['value']) && !empty($field['value']))
                $html .= htmlentities(strip_tags($field['value'], "<p>"));
            if (isset($field['attr']) && is_array($field['attr']))
                $html .= " " . self::getAttr($field['attr']);
        }
        $html .= "/>";
        return $html;
    }

    /**
     * build text element
     *
     * @param $field
     * @param array $languages
     * @return string
     */
    private static function textField($field, $languages = array())
    {
        return self::input($field);
    }

    /**
     * build textarea element
     *
     * @param $field
     * @param string $allowTag
     * @param array $languages
     * @return string
     */
    private static function textAreaField($field, $allowTag = "<p>", $languages = array())
    {
        $html = "<textarea ";
        if (is_array($field['attr']))
            $html .= self::getAttr($field['attr']);
        else
            $html .= $field;
        $html .= " />" . isset($field['value']) ? htmlentities(strip_tags($field['value'], $allowTag = "")) : "";
        $html .= "</textarea>";

        return $html;
    }

    /**
     * build select element with option
     *
     * @param $field
     * @return string
     * @internal param string $allowTag
     */
    private static function selectField($field)
    {
        $html = "";
        $options = array();
        if (isset($field['value']) && !empty($field['value'])) {
            if ($field['value'] instanceof Collection) {
                $options = $field['value']->toArray();
            } else {
                $options = is_array($field['value']) ? $field['value'] : array($field['value']);
            }
        }
        //dd($field);
        //dd($field);
        $html .= "<select name =";
        if (isset($field['name'])) {
            $html .= strtolower($field['name']);
        }
        $html .= " " . self::getAttr($field['attr']);
        $html .= ">";
        //dd($html);
        if (isset($field['displayable']) && count($field['displayable']) > 0) {
            $valueField = trim($field['displayable']['value']);
            $textField = trim($field['displayable']['text']);
        }

        if (is_array($options) && count($options) > 0) {
            foreach ($options as $key => $value) {
                //dd($value);
                if (!empty($valueField) && !empty($textField)) {
                    $html .= "<option value=" . strtolower($value[$valueField]) . " ";
                    if (isset($field['selected']) && strtolower($field['selected']) == strtolower($value[$valueField]))
                        $html .= "selected=\"selected\"";
                    $html .= ">" . htmlentities(strip_tags($value[$textField])) . "</option>";
                } else {
                    $html .= "<option value=" . strtolower($key) . " ";
                    if (isset($field['selected']) && strtolower($field['selected']) == strtolower($key))
                        $html .= "selected=\"selected\"";
                    $html .= ">" . htmlentities(strip_tags($value)) . "</option>";
                }
            }
        } else {
            $html .= "<option>Select one</option>";
        }
        $html .= "</select>";
        return $html;
    }

    /**
     * build check box element
     *
     * @param $field
     * @return string
     */
    private static function checkBoxField($field)
    {
        return self::input($field);
    }

    /**
     * build radio element
     *
     * @param $field
     * @return string
     */
    private static function radioField($field)
    {
        return self::input($field);
    }

    /**
     * build email element
     *
     * @param $field
     * @return string
     */
    private static function emailField($field)
    {
        return self::input($field);
    }

    /**
     * build file element
     *
     * @param $field
     * @return string
     */
    private static function fileField($field)
    {
        return self::input($field);
    }

    /**
     * build password element
     *
     * @param $field
     * @return string
     */
    private static function passwordField($field)
    {
        return self::input($field);
    }

    /**
     * build number element
     *
     * @param $field
     * @return string
     */
    private static function numberField($field)
    {
        return self::input($field);
    }

    /**
     * build date element
     *
     * @param $field
     * @return string
     */
    private static function dateField($field)
    {
        return self::input($field);
    }

    /**
     * buid hidden element
     *
     * @param $field
     * @return string
     */
    private static function hiddenField($field)
    {
        return self::input($field);
    }

    /**
     * build submit element
     *
     * @param $field
     * @return string
     */
    private static function submitButton($field)
    {
        return self::input($field);
    }


}
