<?php

namespace App\Http\Controllers\Traits;

use common\integration\Utility\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait ExportExcelTrait
{

    public function exportcsv($heading, $rows, $filename)
    {
        $filename = $filename . '.csv';
        ob_start();
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $fp = fopen('php://output', 'w');
        $firstLine = 1;

        foreach ($rows as $row) {
            if ($firstLine == 1) {
                fputcsv($fp, $heading);
                $firstLine = 2;
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
        exit;

    }

    public function exportNsaveServer($heading, $rows, $filename, $cjob=false)
    {
        if($cjob) {
            $loc_path = 'admin/cjfiles';
        } else {
            $loc_path = 'admin/exports/' . Auth::user()->id;
        }


        $path = $loc_path;

        $path = File::manipulateBrandResourceDynamicPath($path);

        $this->makeDir($path);
        $storageBasePath = \common\integration\Utility\File::getStoragePath();
        $filename = rand() . '_' . $filename;
        $path = $storageBasePath . '/' . $loc_path . '/' . $filename;
        //echo $path;exit;
        $fullpath = $loc_path . '/' . $filename;

        $fp = fopen($path, 'w');
        $firstLine = 1;

        foreach ($rows as $row) {
            if ($firstLine == 1) {
                fputcsv($fp, $heading);
                $firstLine = 2;
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
        return $fullpath;
    }

}
