<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width initial-scale=1.0">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <link href="{{ Storage::url(config('brand.favicon')) }}" rel="shortcut icon"/>
    <title>{{ config('brand.name') }} | {{__('Admin Panel')}}</title>

    <!-- <link rel="stylesheet" href="{{ asset('css/intlTelInput.css')}}"> -->
    <!-- GLOBAL MAINLY STYLES-->
    <!-- UPDATED BOOTSTRAP-->
    @php

        StyleScript::bootstrapV5MinCss(); // Existing method
        StyleScript::fontAwesomeMinCss(); // New method
        StyleScript::lineAwesomeMinCss(); // New method
        StyleScript::themifyIconsCss(); // New method
        StyleScript::print(\common\integration\Design\StyleAndScript::CSS);

    @endphp
    @include('partials.css_blade.datatables')
    @php
        StyleScript::mainMinCss(); // Existing method
        StyleScript::newstyleCss(); // New method
        StyleScript::customCss_02(); // New method
        StyleScript::customIdentificationCss(); // New method
        StyleScript::mainV5Css(); // Existing method
        StyleScript::brandStylesColors();
        StyleScript::print(\common\integration\Design\StyleAndScript::CSS);

    @endphp


    @yield('css')

    @stack('css')
    @stack('styles')

    <style>
        /*.custome-padding > button.btn, .custome-padding2 > button.btn {*/
        /*    padding: .5rem 2.75rem .5rem 1.25rem !important*/
        /*}*/

        .custome-padding div.bs-actionsbox {
            position: absolute;
            top: 0;
            left: 0;
            width: 90%;
            z-index: 99999999;
            background-color: white;
        }

        .custome-padding ul.dropdown-menu {
            padding-top: 40px !important;
        }

        .bootstrap-select>.dropdown-toggle.btn-default {
            outline: none !important;
        }

        ul.dropdown-menu.inner {
            max-height: 400px !important;
        }

    </style>


        @php

            StyleScript::forcedCss();
            StyleScript::print(\common\integration\Design\StyleAndScript::CSS);

        @endphp



</head>

<body class="fixed-navbar fixed-layout second-layer">
{{--@include('partials.util')--}}
<!-- BEGIN PAGA BACKDROPS-->
{{--<div class="sidenav-backdrop backdrop"></div>--}}
{{--<div class="preloader-backdrop">--}}
{{--    <div class="page-preloader">{{__('Loading')}}</div>--}}
{{--</div>--}}
<!-- END PAGA BACKDROPS-->
<div class="page-wrapper">
    <div class="refresh-spinner text-center" style="display:none;justify-content: center;align-items: center; position: absolute;top:0;left:0;bottom:0; right:0;width:100%;height:100%;z-index:9999;background-color: rgba(255,255,255,0.5)">
        <i class="fa fa-spin fa-spinner text-black" style="font-size: xxx-large;"></i>
    </div>
    <!-- START HEADER-->
@include('partials.ac_header')
<!-- END HEADER-->
    <!-- START SIDEBAR-->
@include('partials.ac_sidebar')
<!-- END SIDEBAR-->
    <div class="content-wrapper">

    @yield('content')


    <!-- END PAGE CONTENT-->
        @include('partials.ac_footer')
    </div>
</div>

<!-- START SEARCH PANEL-->
{{--<form class="search-top-bar" action="search.html">--}}
{{--<input class="form-control search-input" type="text" placeholder="Search...">--}}
{{--<button class="reset input-search-icon"><i class="ti-search"></i></button>--}}
{{--<button class="reset input-search-close" type="button"><i class="ti-close"></i></button>--}}
{{--</form>--}}
<!-- END SEARCH PANEL-->
<!-- BEGIN THEME CONFIG PANEL-->
{{--<div class="theme-config">--}}
{{--<div class="theme-config-toggle"><i class="ti-settings theme-config-show"></i><i class="ti-close theme-config-close"></i></div>--}}
{{--<div class="theme-config-box">--}}
{{--<h5 class="text-center mb-4 mt-3">SETTINGS</h5>--}}
{{--<div class="font-strong mb-3">LAYOUT OPTIONS</div>--}}
{{--<div class="check-list mb-4">--}}
{{--<label class="checkbox checkbox-grey checkbox-primary">--}}
{{--<input id="_fixedNavbar" type="checkbox" checked>--}}
{{--<span class="input-span"></span>Fixed navbar</label>--}}
{{--<label class="checkbox checkbox-grey checkbox-primary mt-3">--}}
{{--<input id="_fixedlayout" type="checkbox">--}}
{{--<span class="input-span"></span>Fixed layout</label>--}}
{{--<label class="checkbox checkbox-grey checkbox-primary mt-3">--}}
{{--<input class="js-sidebar-toggler" type="checkbox">--}}
{{--<span class="input-span"></span>Collapse sidebar</label>--}}
{{--<label class="checkbox checkbox-grey checkbox-primary mt-3">--}}
{{--<input id="_drawerSidebar" type="checkbox">--}}
{{--<span class="input-span"></span>Drawer sidebar</label>--}}
{{--</div>--}}
{{--<div class="font-strong mb-3">LAYOUT STYLE</div>--}}
{{--<div class="check-list mb-4">--}}
{{--<label class="radio radio-grey radio-primary">--}}
{{--<input type="radio" name="layout-style" value="" checked="">--}}
{{--<span class="input-span"></span>Fluid</label>--}}
{{--<label class="radio radio-grey radio-primary mt-3">--}}
{{--<input type="radio" name="layout-style" value="1">--}}
{{--<span class="input-span"></span>Boxed</label>--}}
{{--</div>--}}
{{--</div>--}}
{{--</div>--}}
<!-- END THEME CONFIG PANEL-->

<!-- New question dialog-->
<div class="modal fade" id="session-dialog">
    <div class="modal-dialog" style="width:400px;" role="document">
        <div class="modal-content timeout-modal">
            <div class="modal-body">
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="text-center mt-3 mb-4"><i class="ti-lock timeout-icon"></i></div>
                <div class="text-center h4 mb-3">Set Auto Logout</div>
                <p class="text-center mb-4">You are about to be signed out due to inactivity.<br>Select after how many
                    minutes of inactivity you log out of the system.</p>
                <div id="timeout-reset-box" style="display:none;">
                    <div class="form-group text-center">
                        <button class="btn btn-danger btn-fix btn-air" id="timeout-reset">Deactivate</button>
                    </div>
                </div>
                <div id="timeout-activate-box">
                    <form id="timeout-form" action="javascript:;">
                        <div class="form-group pl-3 pr-3 mb-4">
                            <input class="form-control form-control-line" type="text" name="timeout_count"
                                   placeholder="Minutes" id="timeout-count">
                        </div>
                        <div class="form-group text-center">
                            <button class="btn btn-primary btn-fix btn-air" id="timeout-activate">Activate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End New question dialog-->

@stack('modals')
    <script>
        /*
            ref. ticket:  DEN-939 - bootstrap-select min.js updated from  v1.12.4  to  1.13.14
            afterward some data attributes are breaking the ui which are removed accordingly.
         */
        document.querySelectorAll('.selectpicker').forEach(function(element) {
            element.setAttribute('data-virtual-scroll', 'false');
            element.removeAttribute('data-style');
            element.removeAttribute('data-style-base');
        });
    </script>

    @php
        StyleScript::jqueryMinJs();
        StyleScript::popperMinJs();
        StyleScript::bootstrapV5MinJs();
        StyleScript::metisMenuMinJs();
        StyleScript::jquerySlimscrollMinJs();
        StyleScript::print(\common\integration\Design\StyleAndScript::JS);

    @endphp


<!-- <script src="{{asset('adminca')}}/assets/vendors/jquery-idletimer/dist/idle-timer.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/toastr/toastr.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jquery-validation/dist/jquery.validate.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/js/bootstrap-select.min.js"></script> -->
<!-- PAGE LEVEL PLUGINS-->
<!-- <script src="{{asset('adminca')}}/assets/vendors/chart.js/dist/Chart.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jquery.easy-pie-chart/dist/jquery.easypiechart.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jvectormap/jquery-jvectormap-2.0.3.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/morris.js/morris.min.js"></script> -->
<!-- PAGE LEVEL PLUGINS-->
<!-- <script src="{{asset('adminca')}}/assets/vendors/dataTables/datatables.min.js"></script> -->
@include('partials.js_blade.datatables')
{{--@include('partials.js_blade.datatables-v5')--}}
<!-- <script src="{{asset('adminca')}}/assets/vendors/select2/dist/js/select2.full.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jquery-knob/dist/jquery.knob.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/ion.rangeSlider/js/ion.rangeSlider.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/moment/min/moment.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/smalot-bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script> -->
<!--<script src="{{asset('adminca')}}/assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script>-->
<!-- <script src="{{asset('adminca')}}/assets/vendors/clockpicker/dist/bootstrap-clockpicker.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/jquery-minicolors/jquery.minicolors.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/multiselect/js/jquery.multi-select.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-maxlength/src/bootstrap-maxlength.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-daterangepicker/daterangepicker.js"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script> -->
<!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap-confirmation2/dist/bootstrap-confirmation.min.js"></script> -->
<!-- <script src="{{asset('adminca')}}/assets/vendors/alertifyjs/dist/js/alertify.js"></script> -->
{{-- <script src="{{asset('js/jquery.daterangepicker.min.js')}}"></script> --}}
<!-- CORE SCRIPTS-->

@stack("js-before-app-js")



    @php
        StyleScript::appJs();
        StyleScript::prefixInputJs();
        StyleScript::cleaveMinJs();
        StyleScript::print(\common\integration\Design\StyleAndScript::JS);

    @endphp



<script>

    // prevent duplicate click on form submission
    $('form.preventOnSubmit').submit(function() {
        $(this).find('.disableOnClick').html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);
        console.log("clicked on submit button");
    });


    function deleteAction(formName) {
        alertify.confirm("{{ __('Are you sure?')}}", function () {
            $('#' + formName).submit();
        }, function (event) {
            event.preventDefault();
        });
        $('button.cancel').addClass('btn btn-light').text("{{ __('Cancel')}}");
        $('button.ok').addClass('btn btn-danger').text("{{ __('Yes')}}");
    }

    function pagination(dataContainerId, thisLi, isSearch = null) {
        if (isSearch !== null) {
            myurl = thisLi;
            page = null;
        } else {
            var myurl = thisLi.find('a').attr('href');
            var page = thisLi.find('a').attr('href').split('page=')[1];
            var searchVal = $(dataContainerId).prev('div.ig-search-div').find('input.ig-search-input').val();
            if (searchVal !== '' && searchVal !== undefined && searchVal !== null) {
                myurl += '&search=' + searchVal;
            }
        }
        console.log("new", myurl);
        getData(page, dataContainerId, myurl);
    }

    function getData(page, containerId=null, myurl, appendUrl = true){


        $(containerId).prepend('<i style="position: absolute; left:50%; top: 45%" class="fa fa-spinner fa-spin fa-2x"></i>');


        $.ajax(
            {
                url: myurl,
                type: "get",
                datatype : "html",
                success: function (Response) {
                    if (containerId !==null){
                        $(containerId).empty().html(Response);
                    }
                    if (appendUrl){
                        window.history.pushState(Response, "Title",myurl);
                    }

                    // location.hash = page;
                },
                error: function (Response) {
                    $(".preloader-backdrop").hide();
                    alert('No response from server');
                }
            });
    }

    function resetFromInput(serializedArray) {

        $.each(serializedArray, function (key, input ) {

            var $selector = $('#'+input.name);

            var tagName = $selector.prop("tagName");

            var type = $selector.attr("type");

            if ( tagName== 'select'){

                $selector.find('option').removeAttr("selected");

            }else if (type == "checkbox" || type == "radio"){

                $selector.removeAttr("checked");

            } else {

                $selector.val('');
            }

        });
    }

    function addOrRemoveErrorLabel(key, value = '') {

        var keySelector = $("#" + key +'_error');

        keySelector.html('').removeClass('label label-danger').addClass('d-none').hide();

        if (value){
            keySelector.html(value).addClass('label label-danger').removeClass('d-none').show();
        }
    }

    function removeFormErrors(serializedArray) {
        $.each(serializedArray, function (key, input ) {
            addOrRemoveErrorLabel(input.name);
        });
    }

    function addErrorIntoLabel(error_response) {
        var previous_keys = [];
        $('.modal').find('.border-danger').removeClass('border-danger');
        $.each(error_response, function (key, value) {
            $("#" + key).addClass('border-danger');
            addOrRemoveErrorLabel(key, value)
            // previous_keys.push(key);
        });
    }

    function setValueToSelectBox(dataList, $selectboxSelector) {

        $.each(dataList, function (i, item) {
            // alert(item);
            $selectboxSelector.append($('<option>', {
                value: item,
                text: item
            }));
        });


    }

    function removeValuesOfSelectBox($selectboxSelector , $firstOptinValue = "{{__("Please select")}}") {
        $selectboxSelector.find('option').remove().end().append('<option value="">'+ $firstOptinValue+'</option>');
        $selectboxSelector.trigger("list:updated");
    }


    $('body .notification-pagination a').on('click', function (e) {
        e.preventDefault();
        var pageNo = $(this).html();
        alert(pageNo)
        var Url = "{{route('loadNotification')}}"+'?page_no='+pageNo;

        $.ajax(
            {
                url: Url,
                type: "get",
                datatype : "html",
                success: function (Response) {
                    $('body .notification-pagination').empty().html(Response)
                    // location.hash = page;
                },
                error: function (Response) {
                    $(".preloader-backdrop").hide();
                    alert('No response from server');
                }
            });

    })




{{--    @if(Session::has('success'))--}}
{{--    toastr.success("{{Session::get("success")}}")--}}
{{--    @endif--}}
</script>
<script type="text/javascript">

    //Moment Js Localization
    // moment.locale('{{Config::get('app.locale')}}');

    //Moment JS set Time Zone
    // moment.tz.setDefault("Europe/Istanbul");


    // Read single notification
    $('.single_notification').on('click',function (e) {
        // e.preventDefault();
        var id = $(this).data('notification_id');

        var url = "{{ url(config('constants.defines.ADMIN_URL_SLUG').'/markAsRead') }}/"+id;
        $.get(url);
        var href = $(this).find('a').attr("href");
        window.location.href = href;
    });

    // Mark all notifications as read
    $(".markAllNotification").on("click", function (event) {
        event.preventDefault();
        $(this).html("<i class='fa fa-spinner fa-spin'></i>");
        $(".envelope-badge").html("<i class='fa fa-spinner fa-spin'></i>");
        var url = "{{ url(config('constants.defines.ADMIN_URL_SLUG').'/markAsRead') }}";
        $.get(url, function(data, status) {
            $('.notifications-container').html("");
            $('.notifications-count').html("{{ __("No New Notifications") }}");
            $(".envelope-badge").html("0");
            $(".noti_cnt").html("0 {{ __('New Notifications') }}");
            $(".noti_bdy").html("").hide();
            $(".noti_ftr").html("").hide();
        });
    });

    var currentLang = '<?php echo auth()->user()->language; ?>';
    <!--alert(currentLang);-->
    <!--document.getElementById("languageList").value;-->
    // $('#datepicker1').dateRangePicker({
    //     stickyMonths: true,
    //     startDate: false,
    //     endDate: false,
    //         autoClose: true,
    //     format: 'YYYY/MM/DD',
    //     separator: ' - ',
    //     language: currentLang,
    //     startOfWeek: 'sunday',// or monday
    //     getValue: function()
    //     {
    //         return $(this).val();
    //     },
    //     setValue: function(s)
    //     {
    //         if(s != $(this).val())
    //         {
    //             $(this).val(s);
    //         }
    //     },
    //     time: {
    //         enabled: false
    //     },
    //     minDays: 0,
    //     maxDays: 0,
    //     showShortcuts: true,
    //     shortcuts:
    //     {
    //         //'prev-days': [1,3,5,7],
    //         //'next-days': [3,5,7],
    //         //'prev' : ['week','month','year'],
    //         //'next' : ['week','month','year']
    //     },
    //     customShortcuts : [],
    //     inline:false,
    //     container:'body',
    //     alwaysOpen:false,
    //     singleDate:false,
    //     lookBehind: false,
    //     batchMode: false,
    //     duration: 200,
    //     dayDivAttrs: [],
    //     dayTdAttrs: [],
    //     applyBtnClass: '',
    //     singleMonth: 'auto',
    //     hoveringTooltip: function(days)
    //     {
    //         var D = ['One','Two', 'Three','Four','Five'];
    //         return D[days] ? D[days] + ' days selected' : days + ' days selected';
    //     },
    //     showTopbar: true,
    //     swapTime: false,
    //     selectForward: false,
    //     selectBackward: false,
    //     showWeekNumbers: false,
    //     getWeekNumber: function(date) //date will be the first day of a week
    //     {
    //         return moment(date).format('w');
    //     },
    //     monthSelect: true,
    //     yearSelect: true
    // });
    /* $('#datepicker1').daterangepicker({
        maxDate: new Date(),
        "autoApply": false,
        locale: {
            format: 'YYYY/MM/DD',
            customRangeLabel: "{{__('Custom Range')}}",
            applyLabel: "{{__('Apply')}}",
            cancelLabel: "{{__('Cancel')}}"
        },
        ranges: {
            "{{__('Today')}}": [moment(), moment()],
            "{{__('Yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            "{{__('Last 7 Days')}}": [moment().subtract(6, 'days'), moment()],
            "{{__('Last 30 Days')}}": [moment().subtract(29, 'days'), moment()],
            "{{__('This Month')}}": [moment().startOf('month'), moment().endOf('month')],
            "{{__('Last Month')}}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "alwaysShowCalendars": true,
        "startDate": false,
        "endDate": false
    }); */
</script>
<script>
    {{--  Notification redirection js start  --}}
    $('.notification-item').on('click', function () {
        let url = $(this).children().first().attr('data-url');
        if (url == '#' || typeof(url) == "undefined") {
            window.location = $(this).attr('href')
        } else {
            window.location = $(this).attr('href') + '?redirect=' + url;
        }
    });
    {{--  Notification redirection js end  --}}

    $('#sidebar-collapse').slimScroll({
        width: 'auto',
        height: '100%',
        size: '5px',
        position: 'right',
        color: '#000',
        alwaysVisible: false,
        railVisible: true,
        railColor: '#000',
        railOpacity: 0.1,
        wheelStep: 5,
        allowPageScroll: true,
        disableFadeOut: false
    });

    checkWindowResize();

    $(window).resize(function() {
        checkWindowResize();
    });

    function checkWindowResize() {
        var windowWidth = $(window).width();
        if (windowWidth > 991 && windowWidth < 1219) {
            $(".for-responsive").addClass("col-lg-12");
            $("body").removeClass("drawer-sidebar");
        } else if (windowWidth > 991) {
            $("body").removeClass("drawer-sidebar");
        } else {
            $(".for-responsive").removeClass("col-lg-12");
        }
    }


    function getStatusColorClass(state_id) {
        if(state_id == 3){
            return 'text-info';
        }else if(state_id == 1){
            return 'text-success';
        }else if(state_id == 2){
            return 'text-danger';
        }else {
            return 'text-secondary';
        }
    }

    /* $('.datatable').DataTable({
        'paging'      : true,
        'lengthChange': false,
        'searching'   : false,
        'ordering'    : true,
        'info'        : false,
        'autoWidth'   : false,
        "columnDefs": [{ targets: 'no-sort', orderable: false }],
        'order':[]
    }); */

    function checkFileTypeAndSize(file) {
        var xfile = file.files,
            parent = file.parentElement,
            children = parent.querySelector(".custom-file-label"),
            label = "{{__('No file chosen')}}";
        var sFileName = xfile[0].name;
        var sFileType = sFileName.split('.')[sFileName.split('.').length - 1].toLowerCase();
        var sFileSize = parseFloat(xfile[0].size / 1048576);

        {{--var iFileTypes = '<?php echo json_encode(\App\Models\UserSetting::UPLOAD_FILE_TYPES); ?>';--}}
        var iFileTypes = file.accept.replaceAll('.', '').replaceAll(' ', '').split(',');
        var iFileSize = parseFloat('<?php echo json_encode(\App\Models\UserSetting::UPLOAD_FILE_SIZE); ?>');

        if((iFileTypes.indexOf(sFileType) < 0) || (sFileSize > iFileSize)) {
            xfile[0].value = null;
            var txt = '{{__('Valid file types: $var1 ')}} ' + "\n" + '{{__('Valid file size: $var2 MB')}}';
            txt = txt.replace('$var1', iFileTypes);
            txt = txt.replace("$var2", iFileSize);
            file.value = null;
            alert(txt);
        } else {
            label = sFileName;
        }
        if (typeof children !== 'undefined' && children !== null) {
            children.innerHTML = label;
        }
    }

    // Float Number only check
    $('.float-number-only').on('input', function() {
        let digit_after_decimal = parseInt($(this).attr('digit-after-decimal') ?? 2);
        let fval = this.value;
        fval = fval.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        fval = (fval.indexOf(".") >= 0) ? (fval.substr(0, fval.indexOf(".")) + fval.substr(fval.indexOf("."), digit_after_decimal + 1)) : fval;
        this.value = fval;
    });
    // Number only check
    // $(".number-only").keypress(function(e){
    //     var keyCode = e.which;
    //     if (keyCode < 48 || keyCode > 57) {
    //         return false;
    //     }
    // });

    $(".number-only").bind({

        keypress : function(e){
            var keyCode = e.which;
            if (keyCode < 48 || keyCode > 57) {
                return false;
            }
        },
        paste : function(e){
            var data = e.originalEvent.clipboardData.getData('Text');
            var val = $(this).val();
            var actual_text = val.concat(data)
            $(this).val(actual_text.split(" ").join(""));
            e.preventDefault();
        },

    });

    $("#currency_id").on("change keyup", function () {
        if($(this).val() == "1" || !$(this).val()) {
            $("#sc-div").addClass("d-none");
        } else {
            $("#sc-div").removeClass("d-none");
        }
    });

    $("#logo_img").hide();

    $("#static_bank_id").on("change keyup", function () {
        var bank_name = $(this).find(":selected").text();
        var logo = $(this).find(":selected").data('logo');
        $("#bank_name").val(bank_name);
        if ($.trim(logo).length > 0) {
            var path = "{{ Storage::url('assets/images/bank_logos') }}/" + logo;
            $("#logo_img").attr("src", path);
            $("#logo_img").show();
        } else {
            $("#logo_img").attr("src", '');
            $("#logo_img").hide();
        }
    });

    if(document.getElementById('iban')){
        new Cleave('#iban', {
            numeral : false,
            delimiter: '',
            stripLeadingZeroes : false,
            prefix: 'TR',
            blocks: [2, 24],
        });
    }


    $("#iban").on("keyup", function () {
        var ibanThis = $(this);

        ibanThis.disabled;
        var form = $(this).closest("form").attr("id");
        form = $("#"+form);
        ibanThis.prefix();
        if(ibanThis.val().length != 26) {
            form.attr("disabled", true);
            $("#iban-warning").removeClass("d-none").html("{{ __('IBAN must have 26 characters') }}"); // By IPEK'
        }
        else if(ibanThis.val().length == 26){
            form.removeAttr("disabled");
            $("#iban-warning").addClass("d-none").html("");
        }
        else{
            form.prop("disabled", true)
        }
    });
    $("#iban").on("paste", function() {
        var ibanThis = $("#iban")
        ibanThis.val().split(' ').join('');
    });

    $("#iban").on("blur", function() {
        var ibanThis = $(this);
        var userType = ibanThis.attr("data-userType");

        if (ibanThis.val().length === 26 &&
            (typeof userType !== 'undefined' && userType !== false)) {

            $(".bankLoader").removeClass("d-none");
            ibanThis.prop("disabled", true);
            $.ajax({
                type: "GET",
                url: "",
                dataType: "json",
                data: {'action': 'get_bank_info', 'iban_no': ibanThis.val(), 'user_type': userType},
                success: function (data, textStatus, jQxhr) {
                    if (data.data) {
                        $("#static_bank_id").val(data.data.id);
                        $("#bank_name").val(data.data.name);
                        $("#bankNameHolder").val(data.data.name);
                        var logo = data.data.logo;
                        if ($.trim(logo).length > 0) {
                            var path = logo;
                            $("#logo_img").attr("src", path);
                            $("#logo_img").show();
                        } else {
                            $("#logo_img").attr("src", '');
                            $("#logo_img").hide();
                        }
                    } else {
                        $("#static_bank_id").val('');
                        $("#bank_name").val('');
                        $("#logo_img").attr("src", '');
                        $("#logo_img").hide();
                        $("#bankNameHolder").val('');
                    }
                    $(".bankLoader").addClass("d-none");
                    ibanThis.removeAttr("disabled");
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    // console.log(jqXhr.responseText);
                }
            });
        } else {
            $(".bankLoader").addClass("d-none");
            ibanThis.removeAttr("disabled");
            $("#static_bank_id").val('');
            $("#bank_name").val('');
            $("#logo_img").attr("src", '');
            $("#logo_img").hide();
            $("#bankNameHolder").val('');
        }
    });


    $(document).ready(function() {
        // $("#currency_id").trigger("change");
        $("#static_bank_id").trigger("change");
        $("#iban").trigger("blur");
    });

    function copyToClipboard($id, $title='') {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($("#"+$id).attr("data-url")).select();
        document.execCommand("copy");
        $temp.remove();

        if ($title != '') {
            var _title = $("#"+$id).attr("data-bs-original-title");
            $("#"+$id).attr('data-bs-original-title', $title).tooltip('show');
            $("#"+$id).mouseleave(function () {
                $(this).attr('data-bs-original-title', _title);
            });
        }
    }

    $("#checkStatusBtn").on("click", function (event) {
        event.preventDefault();
        var thisbtn = $(this);
        //fromurl = thisbtn.attr('href');

        var msg = "<strong class='p-0 m-0 d-block'>"+"{{__('Are you sure?')}}"+"</strong>";
        msg += "<p class='p-0 m-0 pl-4 pr-4 text-left'>"+"{{__('This process will check the cashout status from finflow server and will complete the transaction accordingly')}}"+".</p>";

        alertify.confirm(msg, function () {
            thisbtn.html('<i class="fa fa-spin fa-refresh"></i>').addClass('disabled');
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "finflow_check_status";
            input.value = "1";
            var container = document.getElementById("searchboxfrm");
            container.appendChild(input);
            //$("#searchboxfrm").attr('action', fromurl).submit();
            $("#searchboxfrm").submit();
        },function () {
            return false;
        });

        $('button.ok').addClass('btn btn-danger rounded').text("<?php echo e(__('Okay')); ?>");
        $('button.cancel').addClass('btn btn-light rounded').text("<?php echo e(__('Cancel')); ?>");
    });



</script>

@yield('js')
@yield('scripts')
@stack('scripts')

{{--script for other reason input--}}
@include('partials.js_blade.other_reason_js')

@if(\common\integration\BrandConfiguration::disableWindoAnimation())
    @include('js_blades/modal/animation_disable')
@endif

@include('partials.js_blade.input_required_message_override')
@include('partials.js_blade.transaction_revert_reason_js')
@include('js_blades.customized_number_input_js')

@if(\common\integration\BrandConfiguration::newTabDisableWithRrightClick())
<script>

    $('document').ready(function(){

        window.oncontextmenu = function () {
            return false;
        }

        $('a').click(function(e){
            var homeRoute = "{{ route('home') }}";
            var listCtrl = document.getElementsByTagName('a');
            for (var i = 0; i < listCtrl.length; i++) {
                listCtrl[i].onmousedown = function(events) {
                    if (!event) event = window.events;
                    console.log(event);
                    if (event.ctrlKey) { // Ctrl key
                        window.location = homeRoute;
                    }
                    if (event.shiftKey) {   // shift KEY
                        window.location = homeRoute;
                    }
                    if (event.shiftKey && event.ctrlKey) {  // ctrl + shift key
                        window.location = homeRoute;
                    }
                    if (event.which == 2) { // Middle Key
                        window.location = homeRoute;
                    }

                    if (event.which == 3) {
                        return false;
                    }
                }
            }
        });

    });
</script>
<script>
    $(document).mousedown(function(e){
        var homeRoute = "{{ route('home') }}";
        switch(e.which)
        {
            // case 1:
            //     //left Click
            // break;
            case 2:
                window.location = homeRoute;
            break;
            case 3:
                return false;
            break;
        }
        return true;
    });
</script>

@endif

<script>
    let data_selectpicker  = $('.selectpicker');
    if (data_selectpicker.length && typeof data_selectpicker.selectpicker === 'function') {
        $.each(data_selectpicker, function () {
            // console.log($(this).attr("data-title"));
            $(this).attr('title', $(this).attr("data-title"));
            $(this).removeAttr('data-title');
        });
        data_selectpicker.selectpicker('refresh');
    }
</script>
<script>
    $(document).ready(function() {
        $('#close-btn').hide();
    });
    function openQpNav()
    {
        $('#sidebar').css({left: '0'});
        $('#close-btn').show();
    }
    function closeQpNav()
    {
        $('#sidebar').css({left: '-100%'});
    }
</script>
</body>
</html>
