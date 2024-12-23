if ($('#rule_category').val() == '1') {
    $("#payment_source_type_div").show();
} else {
    $("#payment_source_type_div").hide();
}

$("#rule_category").on('change', function() {
    if($('#rule_category').val() == '1') {
        $("#payment_source_type_div").show();
    } else {
        $("#payment_source_type_div").hide();
        $("#payment_source_type").val(0);
    }
    $("#searchboxfrm").submit();
});

$("#payment_source_type").on('change', function() {
    $("#searchboxfrm").submit();
});

$(".select2_demo_1").select2({
    allowClear: true,
    tags: false
});

function toggleAssignMerchantDropdown(value) {
    if (value == 0 || value == 1) {
        $('#merchant').prop("disabled", true);
        $('#merchant_list').hide();
    } else {
        $('#merchant').prop("disabled", false);
        $('#merchant').selectpicker('refresh');
        $('#merchant_list').show();
    }
}

toggleAssignMerchantDropdown($('#merchant_assign').val());

$('#merchant_assign').on('change', function(e) {
    toggleAssignMerchantDropdown(e.target.value);
});


//******** code by jacklin for opertaion type****************/

if( $('#operation_type').val() == 0 ){

    $('#approved_response_code').prop("disabled", true).selectpicker('refresh');
    $('#approved_response_code_wrapper').hide();
    $('#minumum_occurences_approved_for_appdeclined').prop("disabled", true).parent().hide();
    $('#minumum_occurences_declined_for_appdeclined').prop("disabled", true).parent().hide();


}
else{
    if($('#operation_type').val() == 1 ){
        $('#approved_response_code').prop("disabled", true).selectpicker('refresh');
        $('#approved_response_code_wrapper').hide();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", false).parent().show();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", true).parent().hide();

    }
    else if($('#operation_type').val() == 2 ){
        $('#approved_response_code').prop("disabled", false).selectpicker('refresh');
        $('#approved_response_code_wrapper').show();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", false).parent().show();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", false).parent().show();

    }
    else if($('#operation_type').val() == 3 ){
        $('#approved_response_code').prop("disabled", false).selectpicker('refresh');
        $('#approved_response_code_wrapper').show();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", true).parent().hide();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", false).parent().show();


    }


}
$('#operation_type').on('change', function(e){
    if( e.target.value == 1 ){

        $('#approved_response_code').prop("disabled", true).selectpicker('refresh');
        $('#approved_response_code_wrapper').hide();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", false).parent().show();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", true).parent().hide();

    }
    else if( e.target.value == 2 ){

        $('#approved_response_code').prop("disabled", false).selectpicker('refresh');
        $('#approved_response_code_wrapper').show();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", false).parent().show();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", false).parent().show();

    }
    else if( e.target.value == 3 ){

        $('#approved_response_code').prop("disabled", false).selectpicker('refresh');
        $('#approved_response_code_wrapper').show();
        $('#minumum_occurences_approved_for_appdeclined').prop("disabled", true).parent().hide();
        $('#minumum_occurences_declined_for_appdeclined').prop("disabled", false).parent().show();

    }

    else{
        $('#approved_response_code,#minumum_occurences_approved_for_appdeclined,#minumum_occurences_declined_for_appdeclined').prop("disabled", true).parent().hide();

    }
});


$("#approved_response_code").on("change", function(e){
    if(e.target.value != 0){
        $("#minumum_occurences_approved_for_appdeclined").prop("required", true).siblings().find('span').removeClass("d-none");
        $("#minumum_occurences_declined_for_appdeclined").prop("required", true).siblings().find('span').removeClass("d-none");
    }else{
        $("#minumum_occurences_approved_for_appdeclined,#minumum_occurences_declined_for_appdeclined").prop("required", false).siblings().find('span').addClass("d-none");
    }
});


$("#declined_response_code").on("change", function(e){
    if(e.target.value != 0){
        $("#decline_min_occurances").prop("required", true).siblings().find('span').removeClass("d-none");

    }else{
        $("#decline_min_occurances").prop("required", false).siblings().find('span').addClass("d-none");
    }
});

//******** code by jacklin for operation type****************/

$("#card_type").on("change", function(e){
    if(e.target.value != 0){
        $(".card_type_required").removeClass("d-none").parent().siblings('input').prop("required", true);
    }else{
        $(".card_type_required").addClass("d-none").parent().siblings('input').prop("required", false);
    }
});

$('#tr_amount_operator').on('change', function(e){
    if(e.target.value != 0){
        $("#amount_range_from").prop("required", true).siblings().find('span').removeClass("d-none");
    }else{
        $("#amount_range_from").prop("required", false).siblings().find('span').addClass("d-none");
    }

    if( e.target.value == 7 ){
        $('#amount_range_to').prop("required", true).siblings().find('span').removeClass("d-none").parent().show();
    }else{
        $('#amount_range_to').prop("required", false).siblings().find('span').addClass("d-none").parent().hide();
    }
});

if($("#block_type").val() == 1 || $("#block_type").val() == 2){
    $("#country_list").prop("required", false).prop('disabled', true).parent().hide();
    $("#ip_list").prop("required", true).prop('disabled', false).parent().show();
}else{
    if($("#block_type").val() == '0'){
        $("#country_list").prop("required", false).parent().hide();
        $("#ip_list").prop("required", false).parent().hide();
    }else{
        $("#ip_list").prop("required", false).prop('disabled', true).parent().hide();
        $("#country_list").prop("required", true).prop('disabled', false).parent().show();
    }
}

$("#block_type").on("change", function(e){
    if(e.target.value == 1 || e.target.value == 2){
        $("#country_list").prop("required", false).prop('disabled', true).parent().hide();
        $("#ip_list").prop("required", true).prop('disabled', false).parent().show();
    }else{
        if(e.target.value == '0'){
            $("#country_list").prop("required", false).parent().hide();
            $("#ip_list").prop("required", false).parent().hide();
        }else{
            $("#ip_list").prop("required", false).prop('disabled', true).parent().hide();
            $("#country_list").prop("required", true).prop('disabled', false).parent().show();
        }
    }
});

if($("#tr_amount_operator").val() == 7){
    $('#amount_range_to').parent().show();
}else{
    $('#amount_range_to').parent().hide();
}

$('#tr_amount_operator').on('change', function(e){
    if(e.target.value == 7){
        $('#amount_range_to').parent().show();
    }else{
        $('#amount_range_to').parent().hide();
    }
});

if($("#transaction_number_operator").val() == 7){
    $('#total_transaction_number_to').parent().show();
}else{
    $('#total_transaction_number_to').parent().hide();
}
/* // code blocked by jacklin
$("#approved_response_code").on("change", function(e){
    if(e.target.value != 0){
        $("#approve_min_occurance").prop("required", true).siblings().find('span').removeClass("d-none");
    }else{
        $("#approve_min_occurance").prop("required", false).siblings().find('span').addClass("d-none");
    }
});


$("#declined_response_code").on("change", function(e){
    if(e.target.value != 0){
        $("#decline_min_occurance").prop("required", true).siblings().find('span').removeClass("d-none");
    }else{
        $("#decline_min_occurance").prop("required", false).siblings().find('span').addClass("d-none");
    }
});

// code blocked by jacklin
*/




$('#transaction_number_operator').on('change', function(e){
    if(e.target.value == 7){
        $('#total_transaction_number_to').parent().show();
        $('#total_transaction_number_from').parent().show();

        $('#total_transaction_number_from').prop("required", true).prop("disbaled", false);
        $('#total_transaction_number_to').prop("required", true).prop("disbaled", false);

        $('#total_transaction_number_from').siblings('label').find('span').removeClass('d-none');
        $('#total_transaction_number_to').siblings('label').find('span').removeClass('d-none');

    }else if(e.target.value == 0) {
        $('#total_transaction_number_to').parent().hide();
        $('#total_transaction_number_from').parent().hide();

        $('#total_transaction_number_to').prop("required", false).prop("disbaled", true);
        $('#total_transaction_number_from').prop("required", false).prop("disbaled", true);

        $('#total_transaction_number_from').siblings('label').find('span').addClass('d-none');
        $('#total_transaction_number_to').siblings('label').find('span').addClass('d-none');

    }else{
        $('#total_transaction_number_from').parent().show();
        $('#total_transaction_number_from').prop("required", true).prop("disabled", false);
        $('#total_transaction_number_from').siblings('label').find('span').removeClass('d-none');

        $('#total_transaction_number_to').parent().hide().prop("required", false).prop("disabled", true);
        $('#total_transaction_number_to').siblings('label').find('span').addClass('d-none');
    }

});

if($("#transaction_amount_operator").val() == 7){
    $('#transaction_amount_to').parent().show();
}else{
    $('#transaction_amount_to').parent().hide();
}

$('#transaction_amount_operator').on('change', function(e){
    if(e.target.value == 7){
        $('#transaction_amount_to').parent().show();
        $('#transaction_amount_from').parent().show();

        $('#transaction_amount_from').prop("required", true);
        $('#transaction_amount_to').prop("required", true);

    }else if(e.target.value == 0) {
        $('#transaction_amount_to').parent().hide();
        $('#transaction_amount_from').parent().hide();
        $('#transaction_amount_to').prop("required", false);
        $('#transaction_amount_from').prop("required", false);
    }else{
        $('#transaction_amount_from').parent().show();
        $('#transaction_amount_from').prop("required", true);
        $('#transaction_amount_to').parent().hide().prop("required", false);
    }
});

$('#transaction_period_datetime_from').datetimepicker({
    startView: 2,
});
$('#transaction_period_date_from').daterangepicker({
    singleDatePicker: true,
    locale: {
        format: "YYYY-MM-DD"
    }
});
$('#transaction_period_time_from').clockpicker({
    placement: 'top',
    align: 'left',
    donetext: 'Done'
});

$('#transaction_period_datetime_to').datetimepicker({
    startView: 2,
});

$('#transaction_period_date_to').daterangepicker({
    singleDatePicker: true,
    locale: {
        format: "YYYY-MM-DD"
    }
});
$('#transaction_period_time_to').clockpicker({
    placement: 'top',
    align: 'left',
    donetext: 'Done'
});



if($("#tr_period").val() == '1' || $("#tr_period").val() == '2' || $("#tr_period").val() == '3'){
    $('#time_operator').parent().show();
    $('#time_operator').parent().show();
}else{
    $('#time_operator').parent().hide();
    $('#time_operator').parent().hide();
}

// $('#transaction_period_datetime_to').parent().hide();
// $('#transaction_period_datetime_from').parent().hide();

$("#time_operator").on('change', function(e){
    if(e.target.value == 7){
        if($('#tr_period').val() == 1 ){
            $('#transaction_period_date_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_time_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_datetime_to').siblings().hide();

            $('#transaction_period_datetime_to').show().parent().show();
            $('#transaction_period_datetime_to').prop('required', true).prop("disabled", false);
            $('#transaction_period_datetime_to').siblings('label').show().find('span').removeClass('d-none');

        }else if($('#tr_period').val() == 2){
            $('#transaction_period_datetime_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_time_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_date_to').siblings().hide();

            $('#transaction_period_date_to').show().parent().show();
            $('#transaction_period_date_to').prop('required', true).prop('disabled', false);
            $('#transaction_period_date_to').siblings('label').show().find('span').removeClass('d-none');

        }else if($('#tr_period').val() == 3){
            $('#transaction_period_datetime_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_date_to').prop('required', false).prop('disabled', true);
            $('#transaction_period_time_to').siblings().hide();


            $('#transaction_period_time_to').show().parent().show();
            $('#transaction_period_time_to').prop('required', true).prop('disabled', false);
            $('#transaction_period_time_to').siblings('label').show().find('span').removeClass('d-none');
        }

    }else{
        $('#transaction_period_datetime_to').parent().hide();
    }

});


$("#tr_period").on('change',function(e){
    if(e.target.value == 1){

        $('.last_x_days').prop('required', false).prop("disabled", true).parent().hide();
        $('.last_x_hours').prop('required', false).prop("disabled", true).parent().hide();

        $("#time_operator").parent().show();
        $("#time_operator").prop('required', true).siblings().find('span').removeClass('d-none');

        $("#transaction_period_datetime_from").siblings('input').prop("required", false).prop('disabled', true).hide();
        $("#transaction_period_datetime_from").siblings('label').find('span').addClass("d-none");

        $("#transaction_period_datetime_from").prop("required", true).prop('disabled', false).show().parent().show();
        $("#transaction_period_datetime_from").siblings('label').find('span').removeClass("d-none");

        if($("#time_operator").val() == '7'){
            $("#transaction_period_datetime_to").siblings('input').prop("required", false).prop('disabled', true).hide();
            $("#transaction_period_datetime_to").siblings('label').find('span').addClass("d-none");

            $("#transaction_period_datetime_to").prop("required", true).prop('disabled', false).show().parent().show();
            $("#transaction_period_datetime_to").siblings('label').find('span').removeClass("d-none");
        }

    }else if(e.target.value == 2){
        $('.last_x_days').prop('required', false).prop("disabled", true).parent().hide();
        $('.last_x_hours').prop('required', false).prop("disabled", true).parent().hide();

        $("#time_operator").parent().show();
        $("#time_operator").prop('required', true).siblings().find('span').removeClass('d-none');

        $("#transaction_period_date_from").siblings('input').prop("required", false).prop("disabled", true).hide();
        $("#transaction_period_date_from").siblings('label').find('span').addClass("d-none");

        $("#transaction_period_date_from").prop("required", true).prop('disabled', false).show().parent().show();
        $("#transaction_period_date_from").siblings('label').find('span').removeClass("d-none");

        if($("#time_operator").val() == '7'){
            $("#transaction_period_date_to").siblings('input').prop("required", false).prop("disabled", true).hide();
            $("#transaction_period_date_to").siblings('label').find('span').addClass("d-none");

            $("#transaction_period_date_to").prop("required", true).prop('disabled', false).show().parent().show();
            $("#transaction_period_date_to").siblings('label').find('span').removeClass("d-none");
        }

    }else if(e.target.value == 3){
        $('.last_x_days').prop('required', false).prop("disabled", true).parent().hide();
        $('.last_x_hours').prop('required', false).prop("disabled", true).parent().hide();

        $("#time_operator").parent().show();
        $("#time_operator").prop('required', true).siblings().find('span').removeClass('d-none');

        $("#transaction_period_time_from").siblings('input').prop("required", false).prop("disabled", true).hide();
        $("#transaction_period_time_from").siblings('label').find('span').addClass("d-none");

        $("#transaction_period_time_from").prop("required", true).prop("disabled", false).show().parent().show();
        $("#transaction_period_time_from").siblings('label').find('span').removeClass("d-none");

        if($("#time_operator").val() == '7'){
            $("#transaction_period_time_to").siblings('input').prop("required", false).prop("disabled", true).hide();
            $("#transaction_period_time_to").siblings('label').find('span').addClass("d-none");

            $("#transaction_period_time_to").prop("required", true).prop('disabled', false).show().parent().show();
            $("#transaction_period_time_to").siblings('label').find('span').removeClass("d-none");
        }

    }else if(e.target.value == 4){
        $('.last_x_days').prop('required', false).prop("disabled", true).parent().hide();
        $('.last_x_hours').prop('required', false).prop("disabled", true).parent().hide();

        $('#transaction_period_datetime_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_from').prop('required', false).prop("disabled", true);

        $('#transaction_period_datetime_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_to').prop('required', false).prop("disabled", true);

        $(this).parent().siblings().hide();

    }else if(e.target.value == 5){
        $('#transaction_period_datetime_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_from').prop('required', false).prop("disabled", true);

        $('#transaction_period_datetime_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_to').prop('required', false).prop("disabled", true);

        $(this).parent().siblings().hide();
        $('.last_x_days').prop("required", true).prop("disabled", false).parent().show();
        $('.last_x_days').siblings().find('span').removeClass('d-none')

    }else if(e.target.value == 6){
        $('#transaction_period_datetime_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_from').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_from').prop('required', false).prop("disabled", true);

        $('#transaction_period_datetime_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_date_to').prop('required', false).prop("disabled", true);
        $('#transaction_period_time_to').prop('required', false).prop("disabled", true);

        $(this).parent().siblings().hide();
        $('.last_x_hours').prop("required", true).prop("disabled", false).parent().show();
        $('.last_x_hours').siblings().find('span').removeClass('d-none');
    } else if (e.target.value == 0) {
        $(this).parent().siblings().hide();
    }
});
//******** code by jacklin ****************/
// js for 10

if($("#tr_period_time").val() == 1){
    $("#tr_per_number_of_days").siblings("label").text('Number Of Days').parent().show();
    $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);
}
else if($("#tr_period_time").val() == 2){
    $("#tr_per_number_of_days").siblings("label").text('Number Of Hours').parent().show();
    $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);
}
else if($("#tr_period_time").val() == 3){
    $("#tr_per_number_of_days").siblings("label").text('Number Of Minutes').parent().show();
    $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);
}
else{
    $("#tr_per_number_of_days").parent().hide();
    $("#tr_per_number_of_days").prop('required', false).prop("disabled", true);
    $("#tr_per_number_of_days").siblings('label').find('span').addClass('d-none');

}

$("#tr_period_time").on('change', function(e){
    if(e.target.value == 1){
        $("#tr_per_number_of_days").siblings("label").text('Number Of Days').parent().show();
        $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);

    }
    else if(e.target.value == 2){
        $("#tr_per_number_of_days").siblings("label").text('Number Of Hours').parent().show();
        $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);
    }
    else if(e.target.value == 3){
        $("#tr_per_number_of_days").siblings("label").text('Number Of Minutes').parent().show();
        $("#tr_per_number_of_days").prop('required', true).prop('disabled', false);
    }
    else{
        $("#tr_per_number_of_days").parent().hide();

        $("#tr_per_number_of_days").prop('required', false).prop("disabled", true);
        $("#tr_per_number_of_days").siblings('label').find('span').addClass('d-none');



    }
});

// js for 11

if($("#ta_period_time").val() == "1"){
    $("#ta_number_of_days").siblings("label").text('Number Of Days').parent().show();
    $("#ta_number_of_days").prop('required', true).prop('disabled', false);
}
else if($("#ta_period_time").val() == "2"){
    $("#ta_number_of_days").siblings("label").text('Number Of Hours').parent().show();
    $("#ta_number_of_days").prop('required', true).prop('disabled', false);
}
else if($("#ta_period_time").val() == "3"){
    $("#ta_number_of_days").siblings("label").text('Number Of Minutes').parent().show();
    $("#ta_number_of_days").prop('required', true).prop('disabled', false);
}
else{

    $("#ta_number_of_days").parent().hide();
    $("#ta_number_of_days").prop('required', false).prop("disabled", true);
    $("#ta_number_of_days").siblings('label').find('span').addClass('d-none');

}

$("#ta_period_time").on('change', function(e){
    if(e.target.value == "1"){
        $("#ta_number_of_days").siblings("label").text('Number Of Days').parent().show();
        $("#ta_number_of_days").prop('required', true).prop('disabled', false);
    }
    else if(e.target.value == "2"){
        $("#ta_number_of_days").siblings("label").text('Number Of Hours').parent().show();
        $("#ta_number_of_days").prop('required', true).prop('disabled', false);
    }
    else if(e.target.value == "3"){
        $("#ta_number_of_days").siblings("label").text('Number Of Minutes').parent().show();
        $("#ta_number_of_days").prop('required', true).prop('disabled', false);
    }
    else{
        $("#ta_number_of_days").parent().hide();
        $("#ta_number_of_days").prop('required', false).prop("disabled", true);
        $("#ta_number_of_days").siblings('label').find('span').addClass('d-none');

    }
});
//******** code by jacklin ****************/
var isChecked = $("#show_formula").prop('checked');
if(isChecked){
    $("#formula_box").show();
}else{
    $("#formula_box").hide();
}

$("#show_formula").on('change', function(e){
    e.preventDefault();
    var isChecked = $(this).prop('checked');
    if(isChecked){
        $("#formula_box").show();
    }else{
        $("#formula_box").hide();
    }
});

$('#rule_type').on('change', function () {
    let selected_rule_type = $(this).val();
    let show_formula_selector = $('#show_formula');
    let possible_existing_rules_selector = $('#possible-existing-rules-block');
    let ignorable_field_for_rules_in_rules_selector = $('.ignorable_field_for_rules_in_rules');
    if (selected_rule_type === RULE_TYPE_RULES_ON_EXISTING_RULES) {
        $('#rule-attributes').addClass('d-none');
        possible_existing_rules_selector.removeClass('d-none');
        show_formula_selector.prop('checked', true);
        show_formula_selector.prop('disabled', true);
        ignorable_field_for_rules_in_rules_selector.prop('required', false);
    } else if (selected_rule_type == RULE_TYPE_DIFFERENT_CARD) {
        $('#rule-attributes').removeClass('d-none');
        possible_existing_rules_selector.addClass('d-none');
        $('#no-of-different-card-section').removeClass('d-none');
    } else {
        $('#rule-attributes').removeClass('d-none');
        $('#no-of-different-card-section').addClass('d-none');
        possible_existing_rules_selector.addClass('d-none');
        show_formula_selector.prop('checked', false);
        show_formula_selector.prop('disabled', false);
        ignorable_field_for_rules_in_rules_selector.prop('required', true);
    }
    show_formula_selector.trigger('change');
});

$('#possible_existing_rules').on('change', function () {
    let selected_rule_id = $(this).val();
    let formula_box_selector = $('#formula_box');

    if (selected_rule_id !== '') {
        let current_formula = formula_box_selector.val();
        formula_box_selector.val(current_formula + selected_rule_id);
    }

    $(this).val('').selectpicker('refresh');
});
