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
    <div style="margin: 0; width: 100%; height: auto; padding: 0 0 24px 0; background-color: rgb(214,220,228); ">
        <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2">
            <tr>
                <td width="40%">
                    <img src="{{ Storage::url(config('brand.logo')) }}" width="150px" height="auto" style="margin: 0; padding: 4px; border: 2px solid #000000;">
                </td>
                <td width="20%">
                </td>
                <td width="40%" style="padding: 4px;">
                    @php $companyName = strtoupper(config('brand.contact_info.company_full_name')) @endphp
                    <strong>{{ !empty($companyName) ? $companyName : 'Sipay Elektronik Para ve Ödeme Hizmetleri A.Ş.' }}.</strong>
                    <br>Altunizade, Kuşbakışı Cd. No17/2, 34662 Üsküdar/İstanbul, Turkey
                </td>
            </tr>
            <tr>
                <td width="20%">
                </td>
                <td width="30%" align="center" style="padding: 4px; ">
                    <h2 style="margin: 0; padding: 0;">DEKONT</h2>
                </td>
                <td align="right" width="40%">
                    Tarih : {{ $date }} <br>
                    Fiş No : {{ $receiptNo }}
                </td>
            </tr>
        </table>
    </div>
    <div style="margin: 0 0 24px 0; width: 100%; height: auto; padding: 0 0 24px 0; background-color: rgb(208,206,206);">
        <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" >
            <tr>
                <td width="70%">
                    <strong>Ünvan - Ad/Soyad:</strong>  <br><br>
                    <strong>T.C /VKN:</strong><br><br>
                    <strong>Vergi Dairesi:</strong><br><br>
                    <strong>Açıklama:</strong><br><br>
                </td>
                <td width="30%">
                    {{ $merchant->name }} <br><br>
                    @if($merchant->merchant_type == 1)
                        {{ $company->tax_no ?? ''}}
                    @else
                        {{ $company->user->tc_number ?? ''}}
                    @endif
                    <br><br>
                    {{ $company->tax_office }}<br><br>
                    {{ $description }} <br><br>
                </td>
            </tr>
        </table>
    </div>
    <table style="margin: 0 0 48px 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" >
        <tr>
            <th></th>
            <th align="right">Toplam Komisyon Tutarı : {{ $totalCommission }} TL</th>
        </tr>
        <tr>
            <td></td>
            <td align="right">
                {{ $totalCommissionWords }}
            </td>
        </tr>
    </table>
    <table style="margin: 0; padding: 0; width: 100%; height: auto;" width="100%" cellspacing="0" cellpadding="2" >
        <tr>
            <td width="70%">
                Tic. Sic. No : {{ $company->registration_no }}<br><br>
                Ek Bilgi için : {{ $company->phone }}
            </td>
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
