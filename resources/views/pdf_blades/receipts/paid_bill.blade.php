@include('pdf_blades.include.header')

<style>
    .td-label, .td-value {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size:9px;
        color:#555555;
        line-height:1.2;
        padding:15px 10px;
        border-top:1px solid #bbbbbb;
    }
    .td-label {
        text-transform: uppercase;
    }
    .td-title {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        padding:20px 10px;
        font-size:16px;
        color:#555555;
        line-height:1.2;
        text-align: center !important;
        text-transform: uppercase;
    }
</style>
<?php
$product_price = \common\integration\Utility\Number::format($data->total_amount / 100, 2)
?>
<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        <table style="margin:0 auto;padding:0;width:100%;border-collapse:collapse;">
            <tbody>
            <tr>
                <td colspan="4" class="td-title"><strong>{{strtoupper(__('Invoice Payment'))}}</strong></td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{strtoupper(__('Transaction ID'))}}:</strong></td>
                <td class="td-value">{{$data->payment_id}}</td>
                <td class="td-label"><strong>{{strtoupper(__('TRANSACTION DATE'))}}: </strong></td>
                <td class="td-value">{{\App\Utils\Date::format(3,$data->created_at)}}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{__('NAME SURNAME')}}: </strong></td>
                <td class="td-value">{{$data->user_name}}</td>
                <td class="td-label"><strong>{{__('Company Name')}}: </strong></td>
                <td class="td-value">{{$data->association_name}}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{__('Invoice Subscriber Number')}}: </strong></td>
                <td class="td-value">{{$data->subscriber_no}}</td>
                <td class="td-label"><strong>{{strtoupper(__('Payment Method '))}}: </strong></td>
                <td class="td-value">{{ __(\App\Models\PaymentRecOption::PAYMENT_OPTION[$data->payment_method] ?? '') }}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{strtoupper(__('Invoice Amount'))}}: </strong></td>
                <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($product_price, $data->currency->symbol)}}</td>
                <td class="td-label"><strong>{{strtoupper(__('TRANSACTION FEE'))}}: </strong></td>
                <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount(($data->gross-$product_price), $data->currency->symbol)}}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{strtoupper(__('TRANSACTION FEE'))}}: </strong></td>
                <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount(($data->gross-$product_price), $data->currency->symbol)}}</td>
                <td class="td-label"></td>
                <td class="td-value"></td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>

@include('pdf_blades.include.footer')
