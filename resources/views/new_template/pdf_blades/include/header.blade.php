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
</head>
<body style="background:#dedede; font-size:16px; color:#000000; font-family:Arial, Helvetica, sans-serif;">
<table width="600" style="margin:0 auto; padding:20px;" bgcolor="#FFFFFF" cellpadding="0" border="0" cellspacing="0">
    <tbody>
    <tr style="">
        <td style="padding:30px 20px; text-align: center;">
            <p style="padding:30px 20px; text-align: center;">
                <img src="{{ Storage::url(config('brand.logo')) }}"
                     style="width:200px; height:75px;" width="200" height="75"
                     alt="logo"/>
            </p>
        </td>
    </tr>
