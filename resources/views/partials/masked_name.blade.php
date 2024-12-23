<?php

    global $masked_name;
    $name_masking = '';
    if (!empty($name)) {
        $name_masking_array = explode(" ", $name);
        foreach ($name_masking_array as $value) {
            $str = $value[0];
            for ($i = 1; $i < strlen($value); $i++) {
                $str = $str . "*";
            }
            $name_masking .= $str . " ";
        }
    }
    $masked_name = $name_masking;
    echo $name_masking;

?>
