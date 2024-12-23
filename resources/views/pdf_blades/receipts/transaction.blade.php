@include('pdf_blades.include.header')

<style>
    .td-label, .td-value {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size:8px;
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

<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        <table style="margin:0 auto;padding:0;width:100%;border-collapse:collapse;">
            <tbody>
                <tr>
                    <td colspan="4" class="td-title"><strong>{{__('Transaction')}}</strong></td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Transaction ID')}}:</strong></td>
                    <td class="td-value">{{$data['payment_id']}}</td>
                    <td class="td-label"><strong>{{__('TRANSACTION DATE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\Date::format(1,$data['created_at'])}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Name')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedUserName($data['name'], $data['user_category'])}}</td>
                    <td class="td-label"><strong>{{__('ISSUER NAME')}}: </strong></td>
                    <td class="td-value">{{$data['issuer_name']}}</td>
                </tr>
                @if(!\common\integration\BrandConfiguration::hideSomeFieldsOnReceipt())
                <tr>
                    <td class="td-label"><strong>{{__('Net Amount')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data['net'], $data['currency_symbol'])}}</td>
                    <td class="td-label"><strong>{{__('TRANSACTION FEE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data['fee'], $data['currency_symbol'])}}</td>
                </tr>
                @endif
                <tr>
                    <td class="td-label"><strong>{{__('TRANSACTION AMOUNT')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data['gross'], $data['currency_symbol'])}}</td>
                    <td class="td-label"><strong>{{__('STATUS')}}: </strong></td>
                    <td class="td-value">{{ $data['status'] }}</td>
                </tr>
                @if(\common\integration\BrandConfiguration::allowExtraParameterInManualPos())
                    <tr>
                        <td class="td-label"><strong>{{strtoupper(__('Order Id'))}}: </strong></td>
                        <td class="td-value">{{$data['order_id'] ?? ''}}</td>

                        <td class="td-label"><strong>{{strtoupper(__('INSTALLMENT'))}}: </strong></td>
                        <td class="td-value">{{$data['installment'] ?? ''}}</td>
                    </tr>
                @endif
                <tr>
                </tr>
            </tbody>
        </table>
    </td>
</tr>

@include('pdf_blades.include.footer')