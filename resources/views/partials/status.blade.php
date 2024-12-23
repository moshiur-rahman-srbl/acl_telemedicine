<?php

    global $global_status;
    $new_status = "";
    if (!is_null($status)) {
        if ($status == 1) {
            $new_status = "<label style='color:rgb(42,174,54);'>".__('Completed')."</label>";
        } elseif ($status == 2){
            $new_status = "<label style='color:rgb(236,95,104);'>".__('Rejected')."</label>";
        } elseif ($status == 3){
            $new_status = "<label style='color:rgb(243,157,18);'>".__('Pending')."</label>";
        } elseif ($status == 4){
            $new_status = "<label style='color:rgb(44,196,203);'>".__('Stand By')."</label>";
        } elseif ($status == 5){
            $new_status = "<label style='color:rgb(32,90,224);'>".__('Refunded')."</label>";
        } elseif ($status == 6){
            $new_status = "<label style='color:rgb(189,195,199);'>".__('Awaiting')."</label>";
        } elseif ($status == 7){
            $new_status = "<label style='color:rgb(189,195,199);'>".__('Awaiting Refund')."</label>";
        }
    }
    $global_status = $new_status;
    echo $new_status;

?>