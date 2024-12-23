<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <title>{{ config('brand.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #ID16042021__X_Trade_mterinizde {
            width: 614px;
            height: 196px;
            text-align: center;
            font-family: Arial;
            font-style: normal;
            font-weight: normal;
            font-size: 18px;
            color: rgba(96, 96, 96, 1);
        }

        #git_btn {
            border-radius: 20px;
            color: #fff;
            text-align: center;
            font-style: normal;
            font-weight: bold;
            font-size: 16px;
            text-decoration: none;
            padding: 10px 50px;
            background: linear-gradient(90deg, rgba(12, 30, 227, 1) 0%, rgba(166, 14, 223, 1) 88%);
        }

        .greetings {
            text-align: center;
            font-size: 22px;
            padding-bottom: 25px;
            line-height: 1.3rem;
        }

        .brand_name {
            text-align: center;
            font-size: 12px;
            color: rgba(80, 73, 153, 1);
            line-height: 20px;
        }

        .address {
            text-align: center;
            font-weight: normal;
            font-size: 12px;
            color: rgba(96, 96, 96, 1);
            line-height: 25px;
            padding: 5px 10px;
        }
    </style>
    @if(\common\integration\BrandConfiguration::receiptAndEmailContentChanges())
        <style>
            .custom-btn {
                display: inline-block;
                font-weight: 400;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                border: 1px solid transparent;
                padding: .375rem .75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: all .2s ease-in-out;
                color: #fff;
                background-color: #4472C4;
                border-color: #007bff;
            }
            a.custom-btn{
                text-decoration: none;
            }
            .custom-support-btn{
                display: inline-block;
                font-weight: 400;
                text-align: center;
                border: 1px solid transparent;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: .25rem;
                transition: all .2s ease-in-out;
                color: #fff;
                background-color: #874F95;
                border-color: #874F95;
                padding: 10px 110px 10px 110px;
                margin: 26px 120px 9px  140px;
            }
            a.custom-support-btn{
                text-decoration: none;
            }
            .custom-footer-design{
                margin: 0px !important;
                padding: 0px !important;
                text-decoration: none;
                color:#874F95 !important;
            }
        </style>
    @endif
</head>
<body style="background:#dedede; font-size:16px; color:#000000; font-family:Arial, Helvetica, sans-serif;">
<table width="600" style="margin:0 auto; padding:20px;" bgcolor="#FFFFFF" cellpadding="0" border="0" cellspacing="0">
    <tbody>
    <tr style="">
        <td>
            @if(!empty($allow_custom_mail_header_logo))
                @include('email.custom_mail_header_logo_customization_'.config('brand.name_code'))
            @else
            <p style="padding:30px 20px; text-align: center;">
                <img src="{{ Storage::url(config('brand.logo')) }}"
                     style="width:auto; height:75px;" width="200" height="75"
                     alt="logo"/>
            </p>
            @endif
        </td>
    </tr>
