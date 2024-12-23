<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page')</title>
    <style>
        html, body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: normal;
        }
        .wrapper {
            width: 666px;
            height: auto;
            border: 12px solid #000;
            overflow: hidden;
        }
    </style>
</head>

<body id="app">
    <div class="wrapper">
        <div style="margin: 0; width: 100%; height: auto; padding: 0 0 24px 0; background-color: rgb(214,220,228); border-bottom: 2px solid #000;">
            <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td width="40%">
                        <img src="{{ Storage::url(config('brand.logo')) }}" width="150px" height="auto" style="margin: 0; padding: 4px; border: 2px solid #000000;">
                    </td>
                    <td width="20%">
                    </td>
                    <td width="40%" style="padding: 4px; border: 2px solid #000;">
                        <strong>{{ strtoupper(config('brand.contact_info.company_full_name')) }}.</strong>
                        <br>Maslak Mahallesi Bilim Sokak Sun Plaza No:
                        <br>5 A/26 Sarıyer/İstanbul
                        <br>Maslak Vergi Dairesi / 7710528103
                        <br>Mersis No: 0771052810300001
                        <br>Ticaret Sicil No: 166251-5
                    </td>
                </tr>
                <tr>
                    <td width="40%">
                    </td>
                    <td width="20%" align="center" style="padding: 4px; border: 2px solid #000;">
                        <h2 style="margin: 0; padding: 0;">DEKONT</h2>
                    </td>
                    <td width="40%">
                    </td>
                </tr>
            </table>
        </div>
        <div style="margin: 0 0 24px 0; width: 100%; height: auto; padding: 0 0 24px 0; background-color: rgb(208,206,206); border-bottom: 2px solid #000;">
            <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" border="0">
                <tr>
                    <td width="75%">
                        <strong>Müşteri:</strong>
                        @if($tomail == 3)
                            <br>{{$sender['name']}}
                            <br>{{$sender['phone']}}
                            <br>{{$sender['address']}}
                            <br>{{$sender['city']}}
                        @elseif($tomail == 2)
                            <br>{{$receivers['name']}}
                            <br>{{$receivers['phone']}}
                            <br>{{$receivers['address']}}
                            <br>{{$receivers['city']}}
                        @else
                            <br>{{$senders['name']}}
                            <br>{{$senders['phone']}}
                            <br>{{$senders['address']}}
                            <br>{{$senders['city']}}
                        @endif
                    </td>
                    <td width="25%">
                        <strong>Dekontun:</strong>
                        <br>Tarihi: {{date('d/m/Y')}}
                        <br>Saati: {{date('H:i:s')}}
                        @if($tomail == 3)
                            <br>Numarası: {{$wd_id}}
                        @else
                            <br>Numarası: {{$send_transation_id}}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <table style="margin: 0 0 48px 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" border="1">
            <tr>
                <th>İşlem Açıklaması</th>
                {{--<th>Miktarı</th>--}}
                {{--<th>Birim Fiyatı</th>--}}
                <th>Toplam Tutarı</th>
            </tr>
            <tr>
                @if($tomail == 3)
                    <td>{{$sender['msg']}}</td>
                @else
                    <td>{{$msg}}</td>
                @endif
                {{--<td></td>--}}
                {{--<td></td>--}}
                    @if($tomail == 3)
                        <td>{{$sender['amount']}}</td>
                    @else
                        <td>{{$amt}}</td>
                    @endif
            </tr>
        </table>
        <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" border="0">
            <tr>
                <td width="70%"></td>
                <td width="30%">
                    Saygılarımızla
                    <br>{{ config('brand.name') }} ELEKTRONİK PARA VE ÖDEME HİZMETLERİ A.Ş.
                    <br>Elektronik Olarak Onaylanmıştır
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
