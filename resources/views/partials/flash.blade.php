@if(session()->has('flash_message'))
    <?php
        $level_message = session()->get('flash_message_level');
        $flash_message = session()->get('flash_message');
    ?>
    @if(is_array($level_message) && is_array($flash_message))
        @foreach ($level_message as $key => $msg)
            <div class="alert alert-{{$msg}} alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                {!!$flash_message[$key]!!}
            </div>
        @endforeach
    @else
        <div class="alert alert-{{session()->get('flash_message_level')}} alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {!! session()->get('flash_message') !!}
        </div>
    @endif
@endif
@if($errors->any())
    @foreach ($errors->all() as $error)
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {!! $error !!}
        </div>
    @endforeach
@endif

