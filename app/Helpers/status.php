<?php

function status($column_name){
    if($column_name["id"] == 1){
        echo '<span class="badge badge-success">'.__($column_name['name']).'</span>';
    }

    elseif($column_name['id'] == 2){
        echo '<span class="badge badge-danger">'.__($column_name['name']).'</span>';
    }

    elseif($column_name['id'] == 3){
        echo '<span class="badge badge-warning">'.__($column_name['name']).'</span>';
    }

    elseif($column_name['id'] == 4){
        echo '<span class="badge badge-primary">'.__($column_name['name']).'</span>';
    }

    elseif($column_name['id'] == 5){
        echo '<span class="badge badge-danger">'.__($column_name['name']).'</span>';
    }

    elseif ($column_name['id'] == 6){
        echo '<span class="badge badge-secondary">'.__($column_name['name']).'</span>';
    }

}

