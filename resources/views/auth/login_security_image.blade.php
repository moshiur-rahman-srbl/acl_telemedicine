@extends('layouts.ac_login')

<style>
    .red-border {
        border-bottom-color: #f00 !important;
    }
    .sa {
     font-size: 13px;
     color: #219351;
     background-color: #97e6b8;
    }
    .da {
        font-size: 13px;
        color: #a6372b;
        background-color: #f3a69e;
    }
</style>

@section('content')
    <form class="ibox-body" enctype= multipart/form-data id="login-form" action="{{ route('home') }}" method="POST">
        @csrf
        @if (\common\integration\BrandConfiguration::allowSecurityImageInAdminPanel())
            @push('css')
                <style>
                    .security_image{
                        cursor: pointer;
                    }
                    .security_image input[type="radio"]:checked+img{
                        border: 5px solid #131313;
                    }

                </style>
            @endpush

            <div class="form-group mb-4 well text-center">
                <h6>Set Security Picture</h6>
                <div class="row">
                    @foreach($security_images as $images)
                        <div class="col-md-4 radio-toolbar">
                            <label class="security_image" for="radio_{{$images->id}}">
                                <input type="radio" name="security_image" id="radio_{{$images->id}}" value="{{$images->id}}" class="radio_security_image" hidden checked>
                                <img src="{{ Storage::url($images->image_path) }}" title=""/>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            @push('scripts')
                <script>
                    $(".security_image").on("click",function () {
                        $(".radio_security_image").val();
                        $(this).toggleClass('selected');
                    });
                </script>
            @endpush
        @endif
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-primary btn-rounded btn-block text-uppercase">{{__(' Submit ')}}</button>
        </div>
    </form>
@endsection
