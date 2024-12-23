@include('pdf_blades.include.header')

<style>
    .td-label, .td-value {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size:10px;
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
                    <td colspan="4" class="td-title"><strong>{{__('EXCHANGE')}}</strong></td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Transaction ID')}}:</strong></td>
                    <td class="td-value">{{$data->payment_id}}</td>
                    <td class="td-label"><strong>{{__('TRANSACTION DATE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\Date::format(1,$data->created_at)}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Name Surname')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedUserName($data->name, auth()->user()->user_category)}}</td>
                    <td class="td-label"><strong>{{__('EXCHANGE RATE')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->exchange_rate, '', 4)}}</td>
                </tr>
                <tr>
                    <td class="td-label"><strong>{{__('Exchanged From')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->from_amount, $data->first_currency, 2)}}</td>
                    <td class="td-label"><strong>{{__('Exchanged To')}}: </strong></td>
                    <td class="td-value">{{\App\Utils\CommonFunction::getFormatedAmount($data->to_amount, $data->second_currency, 2)}}</td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>

@include('pdf_blades.include.footer')
