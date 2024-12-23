<div>
    <h2>{{__($file_name)}}</h2>

    <table cellpadding="10" class="table table-bordered table-hover" id=""
           style="border-collapse: collapse; font-family: Arial, Times, serif; font-size: 12px;">
        @if(!empty($header))
            <thead>
            <tr>

                @foreach ($header as $head)
                    <th style="border-right: 1px solid #f3f3f3; margin-bottom: 5px; background: silver;">{{__($head)}}</th>
                @endforeach

            </tr>
            </thead>
        @endif
        <tbody>

        {{--{{dd($data[0])}}--}}
        @if(!empty($data))
            @foreach($data as $value)
                <tr>
                    @foreach($value as $v)
                       <td style="border-left: 1px solid #f3f3f3; border-right: 1px solid #f3f3f3;">{{__($v)}}</td>
                    @endforeach

                </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</div>





