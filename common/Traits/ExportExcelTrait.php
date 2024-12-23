<?php

namespace App\Http\Controllers\Traits;

use App\Exports\ExportExcel;
use App\Exports\ExportExcelMultipleSheets;
use App\Models\MerchantReportHistory;
use App\Models\MerchantScheduleReport;
use App\User;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\BrandConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use common\integration\Exports\CardProgramReportExport;
use common\integration\Reports\Formats\Exports\ScheduledReportExport;
use common\integration\ManipulateDate;

trait ExportExcelTrait
{
    public $export_by_fast_excel = false;

    public function exportcsv($heading, $rows, $filename)
    {
        $filename = $filename . '.csv';
        ob_start();
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
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

    public function exportNsaveServer($heading, $rows, $filename)
    {
        $authId = Auth::user()->id ?? 0;
        $path = 'admin/exports/' . $authId;
        $this->makeDir($path);
        $storageBasePath = \common\integration\Utility\File::getStoragePath();
        $filename = rand() . '_' . $filename;
        $path = $storageBasePath . 'admin/exports/' . $authId . '/' . $filename;

        $fullpath = 'admin/exports/' . $authId . '/' . $filename;

        $fp = fopen($path, 'w+') or die('file not created '.$path);
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


    public function exportReportOnServer($header, $data, $id, $format, $type, $view = null, $user_type = User::MERCHANT, $pdf_header = null, $pdf_footer = null, $extras = [],  $title = null , $is_multi_sheets = false)
    {
        if (empty($format)) {
            $format = MerchantReportHistory::FORMAT_CSV;
        }
        $path = 'exportedreports/' . $id;
        if (isset($extras['absolute_path']) && !empty($extras['absolute_path'])) {
            $path = $extras['absolute_path'];
        }
	    
		
	    $path = \common\integration\Utility\File::manipulateBrandResourceDynamicPath($path);
		

        if (isset($extras['path_label_up']) && $extras['path_label_up']) {
            $this->export_by_fast_excel = true;
            if (isset($extras['absolute_path']) && !empty($extras['absolute_path'])) {
                 $this->export_by_fast_excel = false;
            }
            $storagePath = \common\integration\Utility\File::getStoragePath();
            if (!is_dir($storagePath . $path)) {
                mkdir($storagePath . $path, 0777, true);
            }
        } else {
            $storagePath = base_path()."/../public/";
            if (!File::exists($storagePath . $path)) {
                File::makeDirectory($storagePath . $path, $mode = 0777, true, true);
            }
        }


        $file_ext = MerchantReportHistory::FORMAT_LIST[$format] ?? 'csv';

        $length = 32;
        if (! isset($extras['scheduled_report'])) {
            $filename = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length).'.'.$file_ext;
        } else {
            if (isset($extras['report_type'])) {
                $filename = $extras['report_type'] . '.' . $file_ext;
            }
        }

        $path = $storagePath . 'exportedreports/' . $id . '/' . $filename;

        if (isset($extras['absolute_path']) && !empty($extras['absolute_path'])) {
            $file_path = $extras['absolute_path'] . $extras['additional_path'] . '.' . $file_ext;
        } else {
            $file_path = 'exportedreports/'. $id .'/'. $filename;
        }

//        if ($format == MerchantReportHistory::FORMAT_CSV) {
//
//            $fp = fopen($path, 'w+') or die('file not created '.$path);
//            $firstLine = 1;
//
//            foreach ($data as $row) {
//                if ($firstLine == 1) {
//                    fputcsv($fp, $header);
//                    $firstLine = 2;
//                }
//                fputcsv($fp, $row);
//            }
//            fclose($fp);

//        } else
        if ($format == MerchantReportHistory::FORMAT_CSV || $format == MerchantReportHistory::FORMAT_XLS) {
            $max_execution_time = BrandConfiguration::call([BackendMix::class, 'maximumExecutionTimeForReporting']);
            ini_set('max_execution_time',$max_execution_time);
            if ($this->export_by_fast_excel){
                (new FastExcel($data))
                    ->export($path);
            } else {

                if ($is_multi_sheets) {
                    Excel::store(new ExportExcelMultipleSheets($data, $header, $title), $file_path);
                } else if (isset($extras['export_csv_from_view']) && $extras['export_csv_from_view'] === true) {
                    Excel::store(new CardProgramReportExport([
                        'transactions' => $data,
                        'headings' => $header,
                        'date_range' => $extras['date_range']
                    ]), $file_path, 'report');   
                } else if (isset($extras['export_scheduled_report']) && $extras['export_scheduled_report'] == true) {

                    $default_delimeter = config('excel.exports.csv.delimiter');
                    $default_enclosure = config('excel.exports.csv.enclosure');
                    if(!empty($extras['delimeter'])){
                        config()->set('excel.exports.csv.delimiter', $extras['delimeter']);
                    }
                    if(isset($extras['enclosure'])){
                        config()->set('excel.exports.csv.enclosure', $extras['enclosure']);
                    }

                    Excel::store(new ScheduledReportExport([
                        'transactions' => $data,
                        'headings' => $header,
                        'date_range' => $extras['date_range'],
                        'report_type' => $extras['type']
                    ]), $file_path, 'report');

                    if(!empty($extras['delimeter'])){
                        config()->set('excel.exports.csv.delimiter', $default_delimeter);
                    }
                    if(isset($extras['enclosure'])){
                        config()->set('excel.exports.csv.enclosure', $default_enclosure);
                    }

                } else {
                    Excel::store(new ExportExcel(collect($data), $header), $file_path, 'report');
                }
            }


        } elseif ($format == MerchantReportHistory::FORMAT_PDF) {

            $file_name = MerchantReportHistory::RT_LIST[$user_type][$type] ?? 'Transactions';
            $html = view($view, compact('data', 'header', 'file_name'))->render();


            $htmlContentLength = strlen($html) + 1;
            ini_set("pcre.backtrack_limit", $htmlContentLength);

            $pdf = new \Mpdf\Mpdf();
            if ($this->isNonSecureConnection()){
                $pdf->curlAllowUnsafeSslRequests = true;
            }
            if (!empty($pdf_header)){
                $pdf->setAutoTopMargin = 'stretch';
                $pdf->SetHTMLHeader(view($pdf_header)->render());
            }
            if (!empty($pdf_footer)){
                $pdf->setAutoBottomMargin = 'stretch';
                $pdf->SetHTMLFooter(view($pdf_footer)->render());
            }

            $pdf->WriteHTML($html);
            $pdf->Output($path);

        }

        return $file_path;
    }

}
