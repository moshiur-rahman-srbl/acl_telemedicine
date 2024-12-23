
{{-- IF IS THEIR ANY ALRET FOR TAB CLOSE START--}}

{{--@if(view()->exists('partials.css_blade.sweetalert'))--}}
{{--	@include('partials.css_blade.sweetalert')--}}
{{--@endif--}}

{{--@if(view()->exists('partials.js_blade.sweetalert'))--}}
{{--	@include('partials.js_blade.sweetalert')--}}
{{--@endif--}}

{{--@if(view()->exists('new_template.partials.css_blade.sweetalert'))--}}
{{--	@include('new_template.partials.css_blade.sweetalert')--}}
{{--@endif--}}

{{--@if(view()->exists('new_template.partials.js_blade.sweetalert'))--}}
{{--	@include('new_template.partials.js_blade.sweetalert')--}}
{{--@endif--}}

{{-- END --}}

<script type="text/javascript">
	
    // Broadcast that you're opening a page.
    //localStorage.openpages = Date.now();
    //alert(1)
    if(!localStorage?.openpages){
        localStorage.setItem('openpages',1);
        sessionStorage.setItem('tab',1)
    }

    const openpages = localStorage?.openpages;
    const tab = sessionStorage?.getItem('tab');

    if(openpages != tab){
        localStorage.removeItem('openpages')
        $('#logout-form').submit();
    }

   /* var onLocalStorageEvent = function(e){
        if(e.key == "openpages"){
            // Listen if anybody else is opening the same page!
            localStorage.page_available = Date.now();
        }
        if(e.key == "page_available"){
            
            {{-- IF IS THEIR ANY ALRET FOR TAB CLOSE START--}}
            
            {{--swal({--}}
            {{--    title: "{{ __('LogOut Alert')  }}",--}}
            {{--    text: "{{ __('You are not allow for opening new tab') }}",--}}
            {{--    timer: 5000,--}}
            {{--    showCancelButton: false,--}}
            {{--    showConfirmButton: false--}}
            {{--});--}}
            
            {{-- END --}}
            $('#logout-form').submit();
            
            // window.close();
        }
    };
    window.addEventListener('storage', onLocalStorageEvent, false);*/
</script>




