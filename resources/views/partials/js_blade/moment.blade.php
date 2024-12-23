
<script src="{{asset('adminca')}}/assets/vendors/moment/min/moment.min.js"></script>
<script src="{{asset('adminca')}}/assets/vendors/moment/locale/tr.js"></script>

<script>
    //Moment Js Localization
    moment.locale('{{Config::get('app.locale')}}');
</script>
