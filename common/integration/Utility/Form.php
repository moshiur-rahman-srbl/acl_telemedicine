<?php

namespace common\integration\Utility;

class Form
{

    public static function input($key, $value):string
    {
        return "<input type='hidden' name=$key value='$value' >";

    }

    public static function submit($url, $input)
    {
        $res
            =self::formSubmissionJS()
            .self::formStart($url)
            .self::formInputs($input)
            .self::browserInformationInputs()
            .self::collectBrowserInformationJS()
            .self::addNoScript()
            .self::formEnd();

        echo $res;
    //exit for form submission
        exit();
    }

    public static function formSubmissionJS()
    {
        return "
                        <script type='text/javascript'>
                            function hideAndSubmitTimed(formid)
                            {
                            var timer=setTimeout(function() {hideAndSubmit(formid)},10);
                            }
                            
                            function hideAndSubmit(formid)
                            {
                            var formx=document.getElementById(formid);
                                if (formx!=null)
                                {
                                    formx.style.visibility='hidden';
                                    formx.submit();
                                }
                            }
                        </script>
        ";
    }

    public static function collectBrowserInformationJS()
    {
       return  "
                    <script type='text/javascript'>
                        function collectBrowserInformation(formid)
                        {
                            var form=document.getElementById(formid);
                            if (form!=null)
                            {
                                if (form['browserJavascriptEnabled']!=null)
                                {
                                    // if this script runs js is enabled
                                    form['browserJavascriptEnabled'].value='true';
                                }
                                if (form['browserJavaEnabled']!=null)
                                {
                                    form['browserJavaEnabled'].value=navigator.javaEnabled();
                                }
                                if (form['browserColorDepth']!=null)
                                {
                                    form['browserColorDepth'].value=screen.colorDepth;
                                }
                                if (form['browserScreenHeight']!=null)
                                {
                                    form['browserScreenHeight'].value=screen.height;
                                }
                                if (form['browserScreenWidth']!=null)
                                {
                                    form['browserScreenWidth'].value=screen.width;
                                }
                                var timezoneOffsetField=form['browserTZ'];
                                if (timezoneOffsetField!=null)
                                {
                                    timezoneOffsetField.value=new Date().getTimezoneOffset();
                                }
                            }
                        }
                        collectBrowserInformation('the-form');
                    </script>
                    ";
    }




    public static function formStart($url)
    {
        return '<form id="the-form" method="post" action="' . $url . '">';

    }

    public static function formInputs($inputs)
    {
        $form = "";
        foreach ($inputs as $k => $v){
            $form .= Form::input($k, $v);
        }

        return $form;
    }

    public static function formEnd()
    {
        return '</form>';
    }


    public static function browserInformationInputs()
    {
        /*           <input type='hidden' name='browserColorDepth' value=''/>
                    <input type='hidden' name='browserScreenHeight' value=''/>
                    <input type='hidden' name='browserScreenWidth' value=''/>
                    <input type='hidden' name='browserTZ' value=''/>
                    <input type='hidden' name='browserJavascriptEnabled' value=''/>
                    <input type='hidden' name='browserJavaEnabled' value=''/>*/
        return
            "       
                    <script type='text/javascript'>
                        hideAndSubmitTimed('the-form');
                    </script>
              ";
    }

    public static function addNoScript()
    {
        return "
                    <noscript>
                    <div align='center'>
                    <b>Javascript is turned off or not supported!</b>
                    <br/>
                    </div>
                    </noscript>
                    <input type='submit' name='submitBtn' value='Please click here to continue'/>
                ";
    }
}