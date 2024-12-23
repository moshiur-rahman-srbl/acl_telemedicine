<style>
    .tooltip-inner{
        background-color: #5856F4;
        white-space:nowrap;
        max-width:none;
        text-align: center;
    }
    .tooltip-style:hover{
        color: #5856F4;
    }
</style>
<div class="row">
    <div class="col-md-4">
        <div class="totalblnc">
            <h5>{{__('Available Balance Info')}}

                <a href="#" class="tooltip-style text-muted" data-bs-html="true" data-toggle="tooltip" title="{{__('Unsettled Balance')}}
                <?php $walletCount = count($wallets); $i =1; ?>
                @foreach($wallets as $wallet)
                    {{\common\integration\Utility\Number::format($wallet['unsettled_amount'], 2, ' '.$wallet['currency_code'])}}

                    @if($i != $walletCount)
                        |
                    @endif
                    <?php $i++ ?>
                @endforeach
                    </br>
                 {{__('Blocked Balance')}}
                <?php $walletCount = count($wallets); $i =1; ?>
                @foreach($wallets as $wallet)
                {{\common\integration\Utility\Number::format($wallet['block_amount'], 2, ' '.$wallet['currency_code'])}}

                @if($i != $walletCount)
                    |
                @endif
                <?php $i++ ?>
                @endforeach

                @if(isset($merchantid) && !empty($merchantid))
                    </br>
                    {{__('Protected Balance')}}
                    <?php $walletCount = count($wallets); $i =1; ?>
                    @foreach($wallets as $wallet)

                    {{\common\integration\Utility\Number::format($wallet['protected_amount'], 2, ' '.$wallet['currency_code'])}}


                    @if($i != $walletCount)
                        |
                    @endif
                    <?php $i++ ?>

                    @endforeach
                @endif
                ">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </a>

            </h5>

            <div class="blnc_in">
                @if(!empty($wallets))
                    @foreach($wallets as $a_wallet)
                        <span>{{ $a_wallet['currency_symbol'] }}&nbsp;{{number_format($a_wallet['available_amount'] - $a_wallet['block_amount'], 2)}}</span>
                    @endforeach
                @else
                    <span>{{__('No wallet available')}}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="availableblnc">
            <h5>{{__('Total Balance')}}

                <a href="#" class="tooltip-style text-muted" data-bs-html="true" data-toggle="tooltip" title="{{__('Without Rolling Reserve')}}
                <?php $walletCount = count($wallets); $i =1; ?>
                @foreach($wallets as $wallet)
                    {{\common\integration\Utility\Number::format($wallet['total_amount'], 2, ' '.$wallet['currency_code'])}}

                    @if($i != $walletCount)
                            |
                    @endif
                    <?php $i++ ?>
                @endforeach
                        ">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                </a>

            </h5>
            <div class="blnc_in">
                @if(!empty($wallets))
                    @foreach($wallets as $a_wallet)
                        <span>{{ $a_wallet['currency_symbol'] }}&nbsp;{{number_format($a_wallet['total_amount']+$a_wallet['rolling_amount'], 2)}}</span>
                    @endforeach
                @else
                    <span>{{__('No wallet available')}}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="rollingblnc">
            <h5>{{__('Rolling Reserve Balance')}}</h5>
            <div class="blnc_in">
                @if(!empty($wallets))
                    @foreach($wallets as $a_wallet)
                        <span>{{ $a_wallet['currency_symbol'] }}&nbsp;{{number_format($a_wallet['rolling_amount'], 2)}}</span>
                    @endforeach
                @else
                    <span>{{__('No wallet available')}}</span>
                @endif
            </div>
        </div>
    </div>
</div>
