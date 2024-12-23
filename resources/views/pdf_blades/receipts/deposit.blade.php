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
                    <td colspan="4" class="td-title"><strong>{{__('DEPOSIT')}}</strong></td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Transaction ID')}}:</strong></td>
                    <td class="td-value">{{$data->payment_id}}</td>
                    <td class="td-label"><strong>{{__('TRANSACTION DATE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\Date::format(1,$data->created_at)}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Name Surname')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedUserName($data->name, $data->user_category)}}</td>
                    <td class="td-label"><strong>{{__('Bank Name')}}: </strong></td>
                    <td class="td-value">{{$data->bank_name}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('TRANSACTION AMOUNT')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->gross, $data->currency_symbol)}}</td>
                    <td class="td-label"><strong>{{__('DEPOSIT METHOD')}}: </strong></td>
                    <td class="td-value">{{__($data->deposit_method_name)}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('TRANSACTION FEE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->fee, $data->currency_symbol)}}</td>
                    @if($data->deposit_method_id == \App\Models\DepositeMethod::CREDIT_CARD)
                        <td class="td-label"><strong>{{__('CREDIT CARD')}}: </strong></td>
                        <td class="td-value">{{\App\Utils\CommonFunction::creditCardNoMasking($data->credit_card_no, 'deposit')}}</td>
                    @else
                        <td class="td-label"><strong>{{__('IBAN')}}: </strong></td>
                        <td class="td-value">{{$data->iban_no}}</td>
                    @endif
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Net Amount')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->net, $data->currency_symbol)}}</td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>

@include('pdf_blades.include.footer')
