@include('pdf_blades.include.header')

<style>
    .td-label, .td-value {
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size: 8px;
        color: #555555;
        line-height: 1.2;
        padding: 15px 10px;
        border-top: 1px solid #bbbbbb;
    }

    table , th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .td-label {
        text-transform: uppercase;
    }

    .td-title {
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        padding: 20px 10px;
        font-size: 16px;
        color: #555555;
        line-height: 1.2;
        text-align: center !important;
        text-transform: uppercase;
    }
</style>

<tr>
    <td class="td-title">{{__("Pos commission previous data")}}</td>
</tr>
<br/><br/>
<table class="commission_update">
    <tr>
        <th class="td-label">{{__("Pos")}}</th>
        <th class="td-label">{{__("Installment")}}</th>
        <th class="td-label">{{__("Commission")}} (%)</th>
        <th class="td-label">{{__("Commission Fixed")}}</th>
        <th class="td-label">{{__("End User Commission")}} (%)</th>
        <th class="td-label">{{__("End User Commission Fixed")}}</th>
        <th class="td-label">{{__("Status")}}</th>
    </tr>
    @if(!empty($data['PREVIOUS_DATA']))
        @foreach($data['PREVIOUS_DATA'] as $previous_data)
            <tr>
                <td class="td-label">{{ $previous_data['pos_id'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['installment'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['com_percentage'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['com_fixed'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['end_user_com_percentage'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['end_user_com_fixed'] ?? ''}}</td>
                <td class="td-label">{{ $previous_data['status'] == 1 ? __("Active")  : __("InActive")}}</td>
            </tr>
        @endforeach
    @endif
</table>

<br/><br/>
<tr>
    <td class="td-title">{{__("Pos commission updated Data")}}</td>
</tr>
<br/><br/>
<table>
    <tr>
        <th class="td-label">{{__("Pos")}}</th>
        <th class="td-label">{{__("Installment")}}</th>
        <th class="td-label">{{__("Commission")}} (%)</th>
        <th class="td-label">{{__("Commission Fixed")}}</th>
        <th class="td-label">{{__("End User Commission")}} (%)</th>
        <th class="td-label">{{__("End User Commission Fixed")}}</th>
        <th class="td-label">{{__("Status")}}</th>
    </tr>
    @if(!empty($data['UPDATED_DATA']))
        @foreach($data['UPDATED_DATA'] as $updated_data)
            <tr>
                <td class="td-value">{{ $updated_data['pos_id'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['installment'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['com_percentage'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['com_fixed'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['end_user_com_percentage'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['end_user_com_fixed'] ?? ''}}</td>
                <td class="td-value">{{ $updated_data['status'] == 1 ? __("Active")  : __("InActive")}}</td>
            </tr>
        @endforeach
    @endif
</table>


@include('pdf_blades.include.footer')
